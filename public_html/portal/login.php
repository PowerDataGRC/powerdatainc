<?php
/**
 * portal.powerdatainc.com/login.php
 * Client login — username/password + magic link request.
 */

define( 'PD_PORTAL', true );
require_once __DIR__ . '/../wp-load.php';
require_once __DIR__ . '/includes/config.php';

// Redirect already-authenticated clients
if ( pd_current_client() ) {
    header( 'Location: ' . PD_PORTAL_URL . '/files.php' );
    exit;
}

$error        = '';
$magic_sent   = false;
$show_magic   = isset( $_GET['magic'] );
$next         = preg_replace( '/[^a-z0-9\/_\-\.]/i', '', $_GET['next'] ?? '' );

// ── Handle password login ─────────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) ) {

    if ( ! wp_verify_nonce( $_POST['_nonce'] ?? '', 'pd_login' ) ) {
        $error = 'Security check failed. Please try again.';

    } elseif ( $_POST['action'] === 'password_login' ) {
        $result = pd_login(
            sanitize_text_field( $_POST['username'] ?? '' ),
            $_POST['password'] ?? ''
        );
        if ( $result['ok'] ) {
            header( 'Location: ' . ( $next ? PD_PORTAL_URL . '/' . $next : PD_PORTAL_URL . '/files.php' ) );
            exit;
        }
        $error = $result['error'];

    } elseif ( $_POST['action'] === 'magic_link' ) {
        $email  = sanitize_email( $_POST['email'] ?? '' );
        $client = $email ? pd_get_client_by_email( $email ) : null;
        // Always show success to prevent email enumeration
        if ( $client ) {
            pd_mail_magic_link( $client );
            pd_log( 'magic_link_requested', '', $client['id'], $client['username'] );
        }
        $magic_sent = true;
    }
}

$nonce = wp_create_nonce( 'pd_login' );
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Client Portal — <?php echo PD_SITE_NAME; ?></title>
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

      <?php if ( $magic_sent ) : ?>
        <div class="alert alert-success">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
          <div>Check your email — if that address is on file, you'll receive a sign-in link within a few minutes.</div>
        </div>
        <p style="font-size:14px;color:var(--muted);margin-top:8px;">The link expires in <?php echo PD_MAGIC_LINK_LIFETIME / 60; ?> minutes.</p>
        <p style="margin-top:16px;font-size:14px;"><a href="login.php" style="color:var(--accent-deep);font-weight:600;">&#8592; Back to sign in</a></p>

      <?php elseif ( $show_magic ) : ?>

        <h2 style="font-size:20px;margin-bottom:6px;">Email sign-in</h2>
        <p style="color:var(--muted);font-size:14.5px;margin-bottom:24px;">We'll email you a one-click sign-in link.</p>

        <?php if ( $error ) : ?><div class="alert alert-error"><?php echo esc_html( $error ); ?></div><?php endif; ?>

        <form method="post">
          <input type="hidden" name="_nonce"  value="<?php echo esc_attr( $nonce ); ?>">
          <input type="hidden" name="action"  value="magic_link">
          <div class="field">
            <label for="email">Your email address</label>
            <input id="email" name="email" type="email" required placeholder="you@company.com" autocomplete="email">
          </div>
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">Send sign-in link &#8594;</button>
        </form>

        <div class="login-divider">or</div>
        <p style="text-align:center;font-size:14px;"><a href="login.php" style="color:var(--accent-deep);font-weight:600;">Sign in with password</a></p>

      <?php else : ?>

        <h2 style="font-size:20px;margin-bottom:6px;">Sign in</h2>
        <p style="color:var(--muted);font-size:14.5px;margin-bottom:24px;">Enter your credentials to access your files.</p>

        <?php if ( $error ) : ?><div class="alert alert-error"><?php echo esc_html( $error ); ?></div><?php endif; ?>

        <form method="post">
          <input type="hidden" name="_nonce"  value="<?php echo esc_attr( $nonce ); ?>">
          <input type="hidden" name="action"  value="password_login">
          <?php if ( $next ) : ?><input type="hidden" name="next" value="<?php echo esc_attr( $next ); ?>"><?php endif; ?>
          <div class="field">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" required placeholder="your.username" autocomplete="username">
          </div>
          <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" autocomplete="current-password">
          </div>
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">Sign in &#8594;</button>
        </form>

        <div class="login-divider">or</div>
        <p style="text-align:center;">
          <a href="login.php?magic=1" class="btn btn-ghost btn-full" style="font-size:14.5px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
            Email me a sign-in link
          </a>
        </p>

      <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>
