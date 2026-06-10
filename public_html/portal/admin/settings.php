<?php
/**
 * admin.powerdatainc.com/settings.php
 */

define( 'PD_PORTAL', true );
require_once __DIR__ . '/../../wp-load.php';
require_once dirname( __DIR__ ) . '/includes/config.php';

pd_admin_require();

$success = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce( $_POST['_nonce'] ?? '', 'pd_admin_settings' ) ) {
    update_option( 'cf_turnstile_site_key',   sanitize_text_field( $_POST['ts_site_key']   ?? '' ) );
    update_option( 'cf_turnstile_secret_key', sanitize_text_field( $_POST['ts_secret_key'] ?? '' ) );
    $success = 'Settings saved.';
}

$ts_site   = defined( 'CF_TURNSTILE_SITE_KEY' )   ? 'Defined in wp-config.php' : ( get_option( 'cf_turnstile_site_key',   '' ) );
$ts_secret = defined( 'CF_TURNSTILE_SECRET_KEY' )  ? 'Defined in wp-config.php' : ( get_option( 'cf_turnstile_secret_key', '' ) );
$nonce     = wp_create_nonce( 'pd_admin_settings' );

// DB stats
$db_size = file_exists( PD_DB_FILE ) ? round( filesize( PD_DB_FILE ) / 1024, 1 ) . ' KB' : 'N/A';
$storage = 0;
if ( is_dir( PD_STORAGE_DIR ) ) {
    foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( PD_STORAGE_DIR ) ) as $f ) {
        if ( $f->isFile() ) $storage += $f->getSize();
    }
}
$storage_label = $storage >= 1048576 ? round( $storage / 1048576, 1 ) . ' MB' : round( $storage / 1024, 1 ) . ' KB';

include __DIR__ . '/partials/admin-head.php';
?>

<div class="admin-content">
  <div style="margin-bottom:28px;">
    <h1 style="font-size:clamp(22px,3vw,28px);">Settings</h1>
  </div>

  <?php if ( $success ) echo '<div class="alert alert-success"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>' . esc_html( $success ) . '</div>'; ?>

  <!-- Storage info -->
  <div class="card" style="padding:24px;margin-bottom:24px;">
    <h2 style="font-size:16px;margin-bottom:16px;">Storage info</h2>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
      <div>
        <div style="font-size:12px;font-weight:600;letter-spacing:.09em;text-transform:uppercase;color:var(--muted);margin-bottom:5px;">Files directory</div>
        <code style="font-size:12.5px;color:var(--ink-soft);"><?php echo esc_html( PD_STORAGE_DIR ); ?></code>
      </div>
      <div>
        <div style="font-size:12px;font-weight:600;letter-spacing:.09em;text-transform:uppercase;color:var(--muted);margin-bottom:5px;">Total storage used</div>
        <div style="font-family:var(--font-display);font-size:22px;font-weight:700;"><?php echo $storage_label; ?></div>
      </div>
      <div>
        <div style="font-size:12px;font-weight:600;letter-spacing:.09em;text-transform:uppercase;color:var(--muted);margin-bottom:5px;">Database size</div>
        <div style="font-family:var(--font-display);font-size:22px;font-weight:700;"><?php echo $db_size; ?></div>
      </div>
    </div>
  </div>

  <!-- Turnstile keys (only shown if not in wp-config) -->
  <div class="card" style="padding:24px;margin-bottom:24px;">
    <h2 style="font-size:16px;margin-bottom:6px;">Cloudflare Turnstile</h2>
    <p style="color:var(--muted);font-size:14px;margin-bottom:20px;">
      Prefer to set these in <code>wp-config.php</code> as <code>CF_TURNSTILE_SITE_KEY</code> and <code>CF_TURNSTILE_SECRET_KEY</code>.
      Values here are only used if the constants are not defined.
    </p>
    <?php if ( defined( 'CF_TURNSTILE_SITE_KEY' ) ) : ?>
    <div class="alert alert-info">Keys are set via <code>wp-config.php</code> constants — no changes needed here.</div>
    <?php else : ?>
    <form method="post">
      <input type="hidden" name="_nonce" value="<?php echo esc_attr( $nonce ); ?>">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="field"><label>Site key</label><input name="ts_site_key"   type="text" value="<?php echo esc_attr( $ts_site ); ?>"   placeholder="0x4AAAA..."></div>
        <div class="field"><label>Secret key</label><input name="ts_secret_key" type="password" value="<?php echo esc_attr( $ts_secret ); ?>" placeholder="0x4AAAA..."></div>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Save keys</button>
    </form>
    <?php endif; ?>
  </div>

  <!-- WP-Cron status -->
  <div class="card" style="padding:24px;">
    <h2 style="font-size:16px;margin-bottom:6px;">Expiry cron job</h2>
    <p style="color:var(--muted);font-size:14px;margin-bottom:16px;">
      The <code>pd_expire_files</code> WP-Cron event runs daily to mark expired files and send 3-day expiry warnings.
    </p>
    <?php
    $next_run = wp_next_scheduled( 'pd_expire_files' );
    if ( $next_run ) {
        echo '<div class="alert alert-success">Scheduled — next run: ' . esc_html( gmdate( 'M j, Y g:ia', $next_run ) ) . ' UTC</div>';
    } else {
        echo '<div class="alert alert-warning">Not scheduled. Make sure <code>pd-portal-cron.php</code> has been included in your theme\'s functions.php.</div>';
    }
    ?>
  </div>

</div>

<?php include __DIR__ . '/partials/admin-foot.php'; ?>
