<?php
/**
 * portal.powerdatainc.com/magic.php
 * Validates magic link token and creates a session.
 */

define( 'PD_PORTAL', true );
require_once __DIR__ . '/../wp-load.php';
require_once __DIR__ . '/includes/config.php';

$token  = sanitize_text_field( $_GET['token'] ?? '' );
$result = $token ? pd_verify_magic_link( $token ) : [ 'ok' => false, 'error' => 'No token provided.' ];

if ( $result['ok'] ) {
    header( 'Location: ' . PD_PORTAL_URL . '/files.php' );
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sign-in link — <?php echo PD_SITE_NAME; ?></title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="<?php echo PD_PORTAL_URL; ?>/assets/css/portal.css">
</head>
<body>
<div class="login-shell">
  <div class="login-card">
    <div class="login-card-head"><h1><?php echo esc_html( PD_SITE_NAME ); ?></h1><p>Secure Client Portal</p></div>
    <div class="login-card-body">
      <div class="alert alert-error">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
        <div><?php echo esc_html( $result['error'] ); ?></div>
      </div>
      <p style="margin-top:8px;font-size:14px;"><a href="login.php?magic=1" style="color:var(--accent-deep);font-weight:600;">Request a new sign-in link &#8594;</a></p>
    </div>
  </div>
</div>
</body>
</html>
