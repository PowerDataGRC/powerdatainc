<?php
/**
 * PowerData Client Portal — config.php
 * Central configuration. Loaded by every portal entry point.
 */

// ── Prevent direct access outside portal bootstrap ───────────────────────────
if ( ! defined( 'PD_PORTAL' ) ) {
    http_response_code( 403 );
    exit( 'Forbidden' );
}

// ── Paths ─────────────────────────────────────────────────────────────────────
// WordPress root (two levels up from portal/includes/ = public_html/)
define( 'PD_WP_ROOT',      dirname( dirname( dirname( __FILE__ ) ) ) );
define( 'PD_PORTAL_DIR',   dirname( dirname( __FILE__ ) ) );                  // .../portal/
define( 'PD_STORAGE_DIR',  WP_CONTENT_DIR . '/pd-portal-files' );            // outside webroot served area
define( 'PD_DB_FILE',      WP_CONTENT_DIR . '/pd-portal-files/portal.db' );  // SQLite DB

// ── URLs ──────────────────────────────────────────────────────────────────────
define( 'PD_PORTAL_URL',   'https://portal.powerdatainc.com' );
define( 'PD_ADMIN_URL',    'https://admin.powerdatainc.com' );
define( 'PD_SITE_NAME',    'PowerData Solutions' );
define( 'PD_FROM_EMAIL',   'letstalk@powerdatainc.com' );
define( 'PD_FROM_NAME',    'PowerData Portal' );

// ── Security ──────────────────────────────────────────────────────────────────
define( 'PD_SESSION_LIFETIME',    60 * 60 * 8 );   // 8 hours
define( 'PD_MAGIC_LINK_LIFETIME', 60 * 30 );        // 30 minutes
define( 'PD_MAX_FILE_MB',         10 );             // 10 MB per file
define( 'PD_BCRYPT_COST',         12 );

// Allowed MIME types → [extension => mime]
define( 'PD_ALLOWED_TYPES', serialize( [
    'pdf'  => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'md'   => 'text/markdown',
    'html' => 'text/html',
] ) );

// ── Load WordPress (for wp_mail, options, nonces) ────────────────────────────
if ( ! function_exists( 'wp_mail' ) ) {
    require_once PD_WP_ROOT . '/wp-load.php';
}

// ── Autoload portal includes ──────────────────────────────────────────────────
require_once PD_PORTAL_DIR . '/includes/db.php';
require_once PD_PORTAL_DIR . '/includes/auth.php';
require_once PD_PORTAL_DIR . '/includes/files.php';
require_once PD_PORTAL_DIR . '/includes/mailer.php';
