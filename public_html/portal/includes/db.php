<?php
/**
 * PowerData Client Portal — db.php
 * SQLite database layer. All queries go through these functions.
 */

if ( ! defined( 'PD_PORTAL' ) ) { exit; }

// ── Connection singleton ──────────────────────────────────────────────────────
function pd_db(): PDO {
    static $pdo = null;
    if ( $pdo ) return $pdo;

    $dir = PD_STORAGE_DIR;
    if ( ! is_dir( $dir ) ) {
        wp_mkdir_p( $dir );
        // Deny direct HTTP access to storage directory
        file_put_contents( $dir . '/.htaccess', "Deny from all\n" );
        file_put_contents( $dir . '/index.php', '<?php // silence' );
    }

    $pdo = new PDO( 'sqlite:' . PD_DB_FILE, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ] );
    $pdo->exec( 'PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;' );

    pd_db_migrate( $pdo );
    return $pdo;
}

// ── Schema migration (idempotent) ─────────────────────────────────────────────
function pd_db_migrate( PDO $pdo ): void {
    $pdo->exec( <<<SQL

    CREATE TABLE IF NOT EXISTS pd_clients (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        name          TEXT    NOT NULL,
        email         TEXT    NOT NULL UNIQUE,
        username      TEXT    NOT NULL UNIQUE,
        password_hash TEXT    NOT NULL,
        retention_days INTEGER NOT NULL DEFAULT 30,
        active        INTEGER NOT NULL DEFAULT 1,
        created_at    TEXT    NOT NULL DEFAULT (datetime('now')),
        notes         TEXT
    );

    CREATE TABLE IF NOT EXISTS pd_files (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        client_id     INTEGER NOT NULL REFERENCES pd_clients(id) ON DELETE CASCADE,
        original_name TEXT    NOT NULL,
        stored_name   TEXT    NOT NULL UNIQUE,
        mime_type     TEXT    NOT NULL,
        size_bytes    INTEGER NOT NULL,
        label         TEXT,
        expires_at    TEXT    NOT NULL,
        expired       INTEGER NOT NULL DEFAULT 0,
        deleted       INTEGER NOT NULL DEFAULT 0,
        uploaded_at   TEXT    NOT NULL DEFAULT (datetime('now')),
        uploaded_by   TEXT    NOT NULL DEFAULT 'admin'
    );

    CREATE TABLE IF NOT EXISTS pd_sessions (
        id         TEXT    PRIMARY KEY,
        client_id  INTEGER NOT NULL REFERENCES pd_clients(id) ON DELETE CASCADE,
        ip         TEXT,
        user_agent TEXT,
        created_at TEXT    NOT NULL DEFAULT (datetime('now')),
        expires_at TEXT    NOT NULL
    );

    CREATE TABLE IF NOT EXISTS pd_magic_links (
        token      TEXT    PRIMARY KEY,
        client_id  INTEGER NOT NULL REFERENCES pd_clients(id) ON DELETE CASCADE,
        expires_at TEXT    NOT NULL,
        used       INTEGER NOT NULL DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS pd_audit_log (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        client_id  INTEGER REFERENCES pd_clients(id) ON DELETE SET NULL,
        actor      TEXT    NOT NULL,
        action     TEXT    NOT NULL,
        detail     TEXT,
        ip         TEXT,
        created_at TEXT    NOT NULL DEFAULT (datetime('now'))
    );

    CREATE INDEX IF NOT EXISTS idx_files_client   ON pd_files(client_id);
    CREATE INDEX IF NOT EXISTS idx_files_expires  ON pd_files(expires_at);
    CREATE INDEX IF NOT EXISTS idx_sessions_exp   ON pd_sessions(expires_at);

SQL );
}

// ═══════════════════════════════════════════════════════════════════
// CLIENT QUERIES
// ═══════════════════════════════════════════════════════════════════

function pd_get_client( int $id ): ?array {
    $s = pd_db()->prepare( 'SELECT * FROM pd_clients WHERE id = ?' );
    $s->execute( [ $id ] );
    return $s->fetch() ?: null;
}

function pd_get_client_by_username( string $username ): ?array {
    $s = pd_db()->prepare( 'SELECT * FROM pd_clients WHERE username = ? AND active = 1' );
    $s->execute( [ $username ] );
    return $s->fetch() ?: null;
}

function pd_get_client_by_email( string $email ): ?array {
    $s = pd_db()->prepare( 'SELECT * FROM pd_clients WHERE email = ? AND active = 1' );
    $s->execute( [ $email ] );
    return $s->fetch() ?: null;
}

function pd_get_all_clients(): array {
    return pd_db()->query( 'SELECT * FROM pd_clients ORDER BY name ASC' )->fetchAll();
}

function pd_create_client( array $data ): int {
    $hash = password_hash( $data['password'], PASSWORD_BCRYPT, [ 'cost' => PD_BCRYPT_COST ] );
    $s = pd_db()->prepare( <<<SQL
        INSERT INTO pd_clients (name, email, username, password_hash, retention_days, notes)
        VALUES (:name, :email, :username, :hash, :retention, :notes)
SQL );
    $s->execute( [
        ':name'      => $data['name'],
        ':email'     => $data['email'],
        ':username'  => $data['username'],
        ':hash'      => $hash,
        ':retention' => (int) ( $data['retention_days'] ?? 30 ),
        ':notes'     => $data['notes'] ?? null,
    ] );
    return (int) pd_db()->lastInsertId();
}

function pd_update_client( int $id, array $data ): void {
    $fields = [];
    $params = [ ':id' => $id ];

    foreach ( [ 'name', 'email', 'notes' ] as $f ) {
        if ( isset( $data[ $f ] ) ) {
            $fields[]       = "$f = :$f";
            $params[":$f"]  = $data[ $f ];
        }
    }
    if ( isset( $data['retention_days'] ) ) {
        $fields[]              = 'retention_days = :retention_days';
        $params[':retention_days'] = (int) $data['retention_days'];
    }
    if ( isset( $data['active'] ) ) {
        $fields[]          = 'active = :active';
        $params[':active'] = (int) $data['active'];
    }
    if ( isset( $data['password'] ) && $data['password'] !== '' ) {
        $fields[]              = 'password_hash = :password_hash';
        $params[':password_hash'] = password_hash( $data['password'], PASSWORD_BCRYPT, [ 'cost' => PD_BCRYPT_COST ] );
    }
    if ( empty( $fields ) ) return;

    pd_db()->prepare( 'UPDATE pd_clients SET ' . implode( ', ', $fields ) . ' WHERE id = :id' )
           ->execute( $params );
}

// ═══════════════════════════════════════════════════════════════════
// FILE QUERIES
// ═══════════════════════════════════════════════════════════════════

function pd_get_file( int $id ): ?array {
    $s = pd_db()->prepare( 'SELECT * FROM pd_files WHERE id = ? AND deleted = 0' );
    $s->execute( [ $id ] );
    return $s->fetch() ?: null;
}

/**
 * Files visible to the client: not deleted, not expired.
 */
function pd_get_client_files( int $client_id ): array {
    $s = pd_db()->prepare( <<<SQL
        SELECT * FROM pd_files
        WHERE client_id = ? AND deleted = 0 AND expired = 0
        ORDER BY uploaded_at DESC
SQL );
    $s->execute( [ $client_id ] );
    return $s->fetchAll();
}

/**
 * All files for a client (admin view — includes expired).
 */
function pd_get_all_client_files( int $client_id ): array {
    $s = pd_db()->prepare( <<<SQL
        SELECT * FROM pd_files
        WHERE client_id = ? AND deleted = 0
        ORDER BY expired ASC, uploaded_at DESC
SQL );
    $s->execute( [ $client_id ] );
    return $s->fetchAll();
}

function pd_add_file( array $data ): int {
    $s = pd_db()->prepare( <<<SQL
        INSERT INTO pd_files
            (client_id, original_name, stored_name, mime_type, size_bytes, label, expires_at, uploaded_by)
        VALUES
            (:client_id, :original_name, :stored_name, :mime_type, :size_bytes, :label, :expires_at, :uploaded_by)
SQL );
    $s->execute( [
        ':client_id'    => $data['client_id'],
        ':original_name'=> $data['original_name'],
        ':stored_name'  => $data['stored_name'],
        ':mime_type'    => $data['mime_type'],
        ':size_bytes'   => $data['size_bytes'],
        ':label'        => $data['label'] ?? null,
        ':expires_at'   => $data['expires_at'],
        ':uploaded_by'  => $data['uploaded_by'] ?? 'admin',
    ] );
    return (int) pd_db()->lastInsertId();
}

function pd_soft_delete_file( int $id ): void {
    pd_db()->prepare( 'UPDATE pd_files SET deleted = 1 WHERE id = ?' )->execute( [ $id ] );
}

/**
 * Mark files as expired (hidden from client, kept on disk).
 * Returns count of newly expired files.
 */
function pd_expire_due_files(): int {
    $s = pd_db()->prepare( <<<SQL
        UPDATE pd_files
        SET expired = 1
        WHERE expired = 0 AND deleted = 0 AND expires_at <= datetime('now')
SQL );
    $s->execute();
    return $s->rowCount();
}

/**
 * Files expiring within $days that haven't been warned yet.
 * (We use the label field to track warning — crude but no extra column needed.)
 */
function pd_files_expiring_soon( int $days = 3 ): array {
    $s = pd_db()->prepare( <<<SQL
        SELECT f.*, c.email, c.name AS client_name
        FROM pd_files f
        JOIN pd_clients c ON c.id = f.client_id
        WHERE f.expired = 0 AND f.deleted = 0
          AND f.expires_at <= datetime('now', '+' || :days || ' days')
          AND f.expires_at >  datetime('now')
SQL );
    $s->execute( [ ':days' => $days ] );
    return $s->fetchAll();
}

// ═══════════════════════════════════════════════════════════════════
// AUDIT LOG
// ═══════════════════════════════════════════════════════════════════

function pd_log( string $action, string $detail = '', ?int $client_id = null, string $actor = 'system' ): void {
    try {
        pd_db()->prepare( <<<SQL
            INSERT INTO pd_audit_log (client_id, actor, action, detail, ip)
            VALUES (:cid, :actor, :action, :detail, :ip)
SQL )->execute( [
            ':cid'    => $client_id,
            ':actor'  => $actor,
            ':action' => $action,
            ':detail' => $detail,
            ':ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
        ] );
    } catch ( Exception $e ) {
        // Swallow audit log failures — never break user flow
    }
}

function pd_get_audit_log( int $limit = 100, ?int $client_id = null ): array {
    if ( $client_id ) {
        $s = pd_db()->prepare( 'SELECT * FROM pd_audit_log WHERE client_id = ? ORDER BY created_at DESC LIMIT ?' );
        $s->execute( [ $client_id, $limit ] );
    } else {
        $s = pd_db()->prepare( 'SELECT * FROM pd_audit_log ORDER BY created_at DESC LIMIT ?' );
        $s->execute( [ $limit ] );
    }
    return $s->fetchAll();
}
