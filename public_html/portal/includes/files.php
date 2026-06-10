<?php
/**
 * PowerData Client Portal — files.php
 * Upload validation, secure storage, authenticated serving, expiry.
 */

if ( ! defined( 'PD_PORTAL' ) ) { exit; }

// ── Upload a file for a client ─────────────────────────────────────────────
/**
 * @param array  $file        $_FILES['file'] entry
 * @param int    $client_id
 * @param string $label       Human-readable label (optional)
 * @param int    $expires_days Override client default retention
 * @param string $uploaded_by 'admin' or client username
 * @return array  ['ok' => bool, 'error' => string, 'file_id' => int]
 */
function pd_upload_file( array $file, int $client_id, string $label = '', int $expires_days = 0, string $uploaded_by = 'admin' ): array {

    // ── Basic upload error check ───────────────────────────────────────────
    if ( $file['error'] !== UPLOAD_ERR_OK ) {
        $msgs = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server temporary directory missing.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to server.',
        ];
        return [ 'ok' => false, 'error' => $msgs[ $file['error'] ] ?? 'Upload error.' ];
    }

    // ── Size check ────────────────────────────────────────────────────────
    $max_bytes = PD_MAX_FILE_MB * 1024 * 1024;
    if ( $file['size'] > $max_bytes ) {
        return [ 'ok' => false, 'error' => 'File exceeds the ' . PD_MAX_FILE_MB . ' MB limit.' ];
    }

    // ── Extension check ───────────────────────────────────────────────────
    $allowed = unserialize( PD_ALLOWED_TYPES );
    $ext     = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
    if ( ! array_key_exists( $ext, $allowed ) ) {
        return [ 'ok' => false, 'error' => 'File type .' . $ext . ' is not allowed.' ];
    }

    // ── MIME verification (finfo) ─────────────────────────────────────────
    $finfo     = new finfo( FILEINFO_MIME_TYPE );
    $real_mime = $finfo->file( $file['tmp_name'] );

    // Some text/* types come back differently on different systems — be lenient
    $expected_mime = $allowed[ $ext ];
    $mime_ok = ( $real_mime === $expected_mime )
        || ( $ext === 'md'   && str_starts_with( $real_mime, 'text/' ) )
        || ( $ext === 'html' && str_starts_with( $real_mime, 'text/' ) )
        || ( $ext === 'docx' && $real_mime === 'application/zip' ) // finfo sometimes returns zip for docx
        || ( $ext === 'xlsx' && $real_mime === 'application/zip' );

    if ( ! $mime_ok ) {
        return [ 'ok' => false, 'error' => 'File content does not match its extension.' ];
    }

    // ── Determine storage path ────────────────────────────────────────────
    $client_dir = PD_STORAGE_DIR . '/client_' . $client_id;
    if ( ! is_dir( $client_dir ) ) {
        wp_mkdir_p( $client_dir );
        file_put_contents( $client_dir . '/.htaccess', "Deny from all\n" );
    }

    // Stored name: random hex prefix to prevent enumeration
    $stored_name = bin2hex( random_bytes( 16 ) ) . '_' . preg_replace( '/[^a-z0-9._-]/i', '_', $file['name'] );
    $dest_path   = $client_dir . '/' . $stored_name;

    if ( ! move_uploaded_file( $file['tmp_name'], $dest_path ) ) {
        return [ 'ok' => false, 'error' => 'Could not save the file. Please try again.' ];
    }

    // ── Calculate expiry ──────────────────────────────────────────────────
    if ( $expires_days <= 0 ) {
        $client = pd_get_client( $client_id );
        $expires_days = (int) ( $client['retention_days'] ?? 30 );
    }
    $expires_at = gmdate( 'Y-m-d H:i:s', time() + $expires_days * 86400 );

    // ── Save to DB ────────────────────────────────────────────────────────
    $file_id = pd_add_file( [
        'client_id'     => $client_id,
        'original_name' => $file['name'],
        'stored_name'   => $stored_name,
        'mime_type'     => $expected_mime,
        'size_bytes'    => $file['size'],
        'label'         => $label,
        'expires_at'    => $expires_at,
        'uploaded_by'   => $uploaded_by,
    ] );

    pd_log( 'file_upload', $file['name'], $client_id, $uploaded_by );

    return [ 'ok' => true, 'file_id' => $file_id ];
}

// ── Serve a file to the authenticated client ──────────────────────────────
function pd_serve_file( int $file_id, array $client ): void {
    $file = pd_get_file( $file_id );

    if ( ! $file || (int) $file['client_id'] !== (int) $client['id'] ) {
        http_response_code( 404 );
        exit( 'File not found.' );
    }
    if ( $file['expired'] ) {
        http_response_code( 410 );
        exit( 'This file has expired.' );
    }

    $path = PD_STORAGE_DIR . '/client_' . $client['id'] . '/' . $file['stored_name'];
    if ( ! file_exists( $path ) ) {
        http_response_code( 404 );
        exit( 'File not found on server.' );
    }

    pd_log( 'file_download', $file['original_name'], $client['id'], $client['username'] );

    // ── Stream the file ───────────────────────────────────────────────────
    header( 'Content-Type: '        . $file['mime_type'] );
    header( 'Content-Disposition: attachment; filename="' . addslashes( $file['original_name'] ) . '"' );
    header( 'Content-Length: '      . $file['size_bytes'] );
    header( 'Cache-Control: private, no-store' );
    header( 'X-Content-Type-Options: nosniff' );

    readfile( $path );
    exit;
}

// ── Admin serve (no client constraint) ───────────────────────────────────
function pd_admin_serve_file( int $file_id ): void {
    $s = pd_db()->prepare( 'SELECT * FROM pd_files WHERE id = ? AND deleted = 0' );
    $s->execute( [ $file_id ] );
    $file = $s->fetch();

    if ( ! $file ) {
        http_response_code( 404 );
        exit( 'File not found.' );
    }

    $path = PD_STORAGE_DIR . '/client_' . $file['client_id'] . '/' . $file['stored_name'];
    if ( ! file_exists( $path ) ) {
        http_response_code( 404 );
        exit( 'File not found on disk.' );
    }

    pd_log( 'admin_download', $file['original_name'], $file['client_id'], 'admin' );

    header( 'Content-Type: '        . $file['mime_type'] );
    header( 'Content-Disposition: attachment; filename="' . addslashes( $file['original_name'] ) . '"' );
    header( 'Content-Length: '      . $file['size_bytes'] );
    header( 'Cache-Control: private, no-store' );

    readfile( $path );
    exit;
}

// ── Format file size ──────────────────────────────────────────────────────
function pd_format_bytes( int $bytes ): string {
    if ( $bytes >= 1048576 ) return round( $bytes / 1048576, 1 ) . ' MB';
    if ( $bytes >= 1024 )    return round( $bytes / 1024, 1 )    . ' KB';
    return $bytes . ' B';
}

// ── Days until expiry ─────────────────────────────────────────────────────
function pd_days_until_expiry( string $expires_at ): int {
    return max( 0, (int) ceil( ( strtotime( $expires_at ) - time() ) / 86400 ) );
}
