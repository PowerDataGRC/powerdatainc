<?php
/**
 * portal.powerdatainc.com/magic.php
 * Validates magic link token and creates a session.
 *
 * GET  ?token=…  → shows a "Sign in" button (safe for email link scanners)
 * POST with token → verifies + consumes the token (email scanners don't POST)
 */

define( 'PD_PORTAL', true );
require_once __DIR__ . '/../wp-load.php';
require_once __DIR__ . '/includes/config.php';

$token = sanitize_text_field( $_GET['token'] ?? $_POST['token'] ?? '' );
$error = '';

if ( ! $token ) {
    $error = 'No sign-in token provided.';

} elseif ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

    if ( ! wp_verify_nonce( $_POST['_nonce'] ?? '', 'pd_magic_' . $token ) ) {
        $error = 'Security check failed. Please request a new sign-in link.';
    } else {
        $result = pd_verify_magic_link( $token );
        if ( $result['ok'] ) {
            header( 'Location: ' . PD_PORTAL_URL . '/files.php' );
            exit;
        }
        $error = $result['error'];
    }
}

// GET request (or POST failure): render the landing page
$nonce = $token ? wp_create_nonce( 'pd_magic_' . $token ) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sign in — <?php echo esc_html( PD_SITE_NAME ); ?></title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="<?php echo PD_PORTAL_URL; ?>/assets/css/portal.css">
</head>
<body>
<div class="login-shell">
  <div class="login-card">
    <div class="login-card-head">
      <h1><?php echo esc_html( PD_SITE_NAME ); ?></h1>
      <p>Secure Client Portal</p>
    </div>
    <div class="login-card-body">

      <?php if ( $error ) : ?>
        <div class="alert alert-error">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
          <div><?php echo esc_html( $error ); ?></div>
        </div>
        <p style="margin-top:8px;font-size:14px;">
          <a href="login.php?magic=1" style="color:var(--accent-deep);font-weight:600;">Request a new sign-in link &rarr;</a>
        </p>

      <?php elseif ( $token ) : ?>
        <h2 style="font-size:20px;margin-bottom:8px;">Ready to sign in</h2>
        <p style="color:var(--muted);font-size:14.5px;margin-bottom:24px;">
          Click the button below to access your file portal.
        </p>
        <form method="post" action="magic.php">
          <input type="hidden" name="token"  value="<?php echo esc_attr( $token ); ?>">
          <input type="hidden" name="_nonce" value="<?php echo esc_attr( $nonce ); ?>">
          <button type="submit" class="btn btn-primary btn-full">Sign in to portal &rarr;</button>
        </form>
        <p style="margin-top:16px;font-size:13px;color:var(--muted);">
          This link expires in <?php echo PD_MAGIC_LINK_LIFETIME / 60; ?> minutes and can only be used once.
        </p>

      <?php else : ?>
        <div class="alert alert-error">Invalid sign-in link.</div>
        <p style="margin-top:8px;font-size:14px;">
          <a href="login.php?magic=1" style="color:var(--accent-deep);font-weight:600;">Request a new sign-in link &rarr;</a>
        </p>
      <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>
