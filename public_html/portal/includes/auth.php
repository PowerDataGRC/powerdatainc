<?php
/**
 * PowerData Client Portal — auth.php
 * Session management, login, magic-link generation and verification.
 */

if ( ! defined( 'PD_PORTAL' ) ) { exit; }

// ── Session bootstrap ─────────────────────────────────────────────────────────
function pd_session_start(): void {
    if ( session_status() === PHP_SESSION_ACTIVE ) return;

    $secure   = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off';
    $samesite = 'Strict';

    session_set_cookie_params( [
        'lifetime' => PD_SESSION_LIFETIME,
        'path'     => '/',
        'domain'   => '.powerdatainc.com',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => $samesite,
    ] );
    session_name( 'pd_portal_sess' );
    session_start();
}

// ── Get the currently-authenticated client (null if not logged in) ────────────
function pd_current_client(): ?array {
    pd_session_start();

    $sess_id = $_SESSION['pd_sess'] ?? null;
    if ( ! $sess_id ) return null;

    $s = pd_db()->prepare( <<<SQL
        SELECT s.client_id FROM pd_sessions s
        WHERE s.id = ? AND s.expires_at > datetime('now')
SQL );
    $s->execute( [ $sess_id ] );
    $row = $s->fetch();
    if ( ! $row ) {
        unset( $_SESSION['pd_sess'] );
        return null;
    }

    return pd_get_client( (int) $row['client_id'] );
}

// ── Require auth — redirect to login if not authenticated ────────────────────
function pd_require_auth( string $redirect_to = '' ): array {
    $client = pd_current_client();
    if ( ! $client ) {
        $url = PD_PORTAL_URL . '/login.php';
        if ( $redirect_to ) $url .= '?next=' . urlencode( $redirect_to );
        header( 'Location: ' . $url );
        exit;
    }
    return $client;
}

// ── Create a server-side session for a client ─────────────────────────────────
function pd_create_session( int $client_id ): string {
    $id = bin2hex( random_bytes( 32 ) );
    pd_db()->prepare( <<<SQL
        INSERT INTO pd_sessions (id, client_id, ip, user_agent, expires_at)
        VALUES (:id, :cid, :ip, :ua, datetime('now', '+' || :secs || ' seconds'))
SQL )->execute( [
        ':id'   => $id,
        ':cid'  => $client_id,
        ':ip'   => $_SERVER['REMOTE_ADDR'] ?? '',
        ':ua'   => substr( $_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255 ),
        ':secs' => PD_SESSION_LIFETIME,
    ] );
    return $id;
}

// ── Password-based login ──────────────────────────────────────────────────────
function pd_login( string $username, string $password ): array {
    $client = pd_get_client_by_username( $username );

    if ( ! $client || ! password_verify( $password, $client['password_hash'] ) ) {
        pd_log( 'login_failed', "username=$username" );
        return [ 'ok' => false, 'error' => 'Incorrect username or password.' ];
    }

    pd_session_start();
    $_SESSION['pd_sess'] = pd_create_session( $client['id'] );
    pd_log( 'login_success', '', $client['id'], $client['username'] );

    return [ 'ok' => true, 'client' => $client ];
}

// ── Logout ────────────────────────────────────────────────────────────────────
function pd_logout(): void {
    pd_session_start();
    $sess_id = $_SESSION['pd_sess'] ?? null;
    if ( $sess_id ) {
        pd_db()->prepare( 'DELETE FROM pd_sessions WHERE id = ?' )->execute( [ $sess_id ] );
    }
    session_destroy();
    setcookie( 'pd_portal_sess', '', time() - 3600, '/', '.powerdatainc.com', true, true );
}

// ── Magic link generation ─────────────────────────────────────────────────────
function pd_create_magic_link( int $client_id ): string {
    // Invalidate any existing unused tokens for this client
    pd_db()->prepare( 'DELETE FROM pd_magic_links WHERE client_id = ? AND used = 0' )
           ->execute( [ $client_id ] );

    $token = bin2hex( random_bytes( 32 ) );
    pd_db()->prepare( <<<SQL
        INSERT INTO pd_magic_links (token, client_id, expires_at)
        VALUES (:token, :cid, datetime('now', '+' || :secs || ' seconds'))
SQL )->execute( [
        ':token' => $token,
        ':cid'   => $client_id,
        ':secs'  => PD_MAGIC_LINK_LIFETIME,
    ] );

    return PD_PORTAL_URL . '/magic.php?token=' . $token;
}

// ── Magic link verification ───────────────────────────────────────────────────
function pd_verify_magic_link( string $token ): array {
    $s = pd_db()->prepare( <<<SQL
        SELECT * FROM pd_magic_links
        WHERE token = ? AND used = 0 AND expires_at > datetime('now')
SQL );
    $s->execute( [ $token ] );
    $row = $s->fetch();

    if ( ! $row ) {
        return [ 'ok' => false, 'error' => 'This link is invalid or has expired. Please request a new one.' ];
    }

    // Mark used
    pd_db()->prepare( 'UPDATE pd_magic_links SET used = 1 WHERE token = ?' )->execute( [ $token ] );

    $client = pd_get_client( (int) $row['client_id'] );
    if ( ! $client ) {
        return [ 'ok' => false, 'error' => 'Account not found.' ];
    }

    pd_session_start();
    $_SESSION['pd_sess'] = pd_create_session( $client['id'] );
    pd_log( 'magic_link_used', '', $client['id'], $client['username'] );

    return [ 'ok' => true, 'client' => $client ];
}

// ── Admin session (separate — stored in WP user meta) ────────────────────────
function pd_admin_check(): bool {
    // The admin panel is behind WordPress authentication.
    // We simply check that the current WP user has manage_options capability.
    if ( ! function_exists( 'current_user_can' ) ) return false;
    return is_user_logged_in() && current_user_can( 'manage_options' );
}

function pd_admin_require(): void {
    if ( ! pd_admin_check() ) {
        header( 'Location: ' . wp_login_url( PD_ADMIN_URL ) );
        exit;
    }
}
