<?php
/**
 * admin.powerdatainc.com/index.php
 * Admin dashboard — stats and recent activity.
 */

define( 'PD_PORTAL', true );
require_once __DIR__ . '/../../wp-load.php';
require_once dirname( __DIR__ ) . '/includes/config.php';

pd_admin_require();

// Stats
$db      = pd_db();
$clients = (int) $db->query( 'SELECT COUNT(*) FROM pd_clients WHERE active = 1' )->fetchColumn();
$files   = (int) $db->query( 'SELECT COUNT(*) FROM pd_files WHERE deleted = 0 AND expired = 0' )->fetchColumn();
$expired = (int) $db->query( 'SELECT COUNT(*) FROM pd_files WHERE expired = 1 AND deleted = 0' )->fetchColumn();
$expiring_soon = count( pd_files_expiring_soon( 3 ) );
$recent_log    = pd_get_audit_log( 20 );

include __DIR__ . '/partials/admin-head.php';
?>

<div class="admin-content">
  <div style="margin-bottom:32px;">
    <h1 style="font-size:clamp(22px,3vw,28px);">Dashboard</h1>
    <p style="color:var(--muted);font-size:14.5px;margin-top:4px;">Client portal overview</p>
  </div>

  <!-- Stat cards -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="label">Active clients</div>
      <div class="value"><?php echo $clients; ?></div>
      <div class="sub"><a href="clients.php" style="color:var(--accent-deep);font-weight:600;">Manage →</a></div>
    </div>
    <div class="stat-card">
      <div class="label">Active files</div>
      <div class="value"><?php echo $files; ?></div>
      <div class="sub">across all clients</div>
    </div>
    <div class="stat-card">
      <div class="label">Expiring in 3 days</div>
      <div class="value" style="color:<?php echo $expiring_soon > 0 ? 'var(--amber)' : 'inherit'; ?>"><?php echo $expiring_soon; ?></div>
      <div class="sub"><?php echo $expiring_soon > 0 ? '<a href="files.php" style="color:var(--amber);font-weight:600;">Review →</a>' : 'None'; ?></div>
    </div>
    <div class="stat-card">
      <div class="label">Expired (on hold)</div>
      <div class="value" style="color:var(--muted);"><?php echo $expired; ?></div>
      <div class="sub">hidden from clients</div>
    </div>
  </div>

  <!-- Expiry warnings -->
  <?php if ( $expiring_soon > 0 ) :
    $warn_files = pd_files_expiring_soon( 3 );
  ?>
  <div class="alert alert-warning" style="margin-bottom:28px;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <div>
      <strong><?php echo $expiring_soon; ?> file<?php echo $expiring_soon > 1 ? 's' : ''; ?> expire within 3 days.</strong>
      <?php foreach ( $warn_files as $wf ) : ?>
        <div style="font-size:13px;margin-top:4px;"><?php echo esc_html( $wf['client_name'] ); ?> — <em><?php echo esc_html( $wf['original_name'] ); ?></em> (<?php echo pd_days_until_expiry( $wf['expires_at'] ); ?>d)</div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Recent activity -->
  <div class="card" style="overflow:hidden;">
    <div style="padding:20px 20px 0;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;">
      <h2 style="font-size:17px;padding-bottom:16px;">Recent activity</h2>
    </div>
    <?php if ( empty( $recent_log ) ) : ?>
      <div class="empty-state"><p>No activity yet.</p></div>
    <?php else : ?>
    <table class="pd-table" aria-label="Recent audit log">
      <thead>
        <tr>
          <th>Action</th><th>Client / Actor</th><th>Detail</th><th>IP</th><th>When</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $recent_log as $entry ) :
          $action_labels = [
            'login_success'          => ['Login',         'pill-green'],
            'login_failed'           => ['Failed login',  'pill-red'],
            'file_upload'            => ['Upload',        'pill-green'],
            'file_download'          => ['Download',      'pill-green'],
            'admin_download'         => ['Admin DL',      'pill-muted'],
            'magic_link_requested'   => ['Magic link req','pill-muted'],
            'magic_link_used'        => ['Magic link in', 'pill-green'],
            'client_created'         => ['New client',    'pill-green'],
            'client_updated'         => ['Client updated','pill-muted'],
          ];
          [$al, $ac] = $action_labels[ $entry['action'] ] ?? [ $entry['action'], 'pill-muted' ];
        ?>
        <tr>
          <td><span class="pill <?php echo $ac; ?>"><?php echo esc_html( $al ); ?></span></td>
          <td style="color:var(--muted);font-size:13.5px;"><?php echo esc_html( $entry['actor'] ); ?></td>
          <td style="color:var(--muted);font-size:13px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html( $entry['detail'] ); ?></td>
          <td style="color:var(--muted);font-size:13px;font-family:monospace;"><?php echo esc_html( $entry['ip'] ); ?></td>
          <td style="color:var(--muted);font-size:13px;white-space:nowrap;"><?php echo esc_html( gmdate( 'M j, g:ia', strtotime( $entry['created_at'] ) ) ); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/partials/admin-foot.php'; ?>
