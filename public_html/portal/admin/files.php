<?php
/**
 * admin.powerdatainc.com/files.php
 * Upload files to client folders, view all files including expired.
 */

define( 'PD_PORTAL', true );
require_once __DIR__ . '/../../wp-load.php';
require_once dirname( __DIR__ ) . '/includes/config.php';

pd_admin_require();

$error   = '';
$success = '';

// ── Handle download ───────────────────────────────────────────────────────
if ( isset( $_GET['download'] ) && wp_verify_nonce( $_GET['_nonce'] ?? '', 'pd_admin_dl_' . (int)$_GET['download'] ) ) {
    pd_admin_serve_file( (int) $_GET['download'] );
}

// ── Handle POST actions ───────────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) ) {
    if ( ! wp_verify_nonce( $_POST['_nonce'] ?? '', 'pd_admin_files' ) ) {
        $error = 'Security check failed.';
    } else {
        switch ( $_POST['action'] ) {

            case 'upload':
                $client_id = (int) ( $_POST['client_id'] ?? 0 );
                if ( $client_id && ! empty( $_FILES['file'] ) ) {
                    $result = pd_upload_file(
                        $_FILES['file'],
                        $client_id,
                        sanitize_text_field( $_POST['label'] ?? '' ),
                        (int) ( $_POST['expires_days'] ?? 0 ),
                        'admin'
                    );
                    $success = $result['ok'] ? 'File uploaded.' : '';
                    $error   = $result['ok'] ? '' : $result['error'];
                } else {
                    $error = 'Please select a client and a file.';
                }
                break;

            case 'delete':
                $id = (int) ( $_POST['file_id'] ?? 0 );
                $f  = pd_get_file( $id );
                if ( $f ) {
                    pd_soft_delete_file( $id );
                    pd_log( 'admin_file_deleted', $f['original_name'], $f['client_id'], 'admin' );
                    $success = 'File removed from client view (kept on server).';
                }
                break;
        }
    }
}

$clients    = pd_get_all_clients();
$filter_cid = isset( $_GET['client'] ) ? (int) $_GET['client'] : 0;
$nonce      = wp_create_nonce( 'pd_admin_files' );
$allowed    = array_keys( unserialize( PD_ALLOWED_TYPES ) );

// Build file list
if ( $filter_cid ) {
    $file_rows = pd_get_all_client_files( $filter_cid );
    $filter_client = pd_get_client( $filter_cid );
} else {
    // All files across all clients
    $s = pd_db()->query( <<<SQL
        SELECT f.*, c.name AS client_name, c.username AS client_username
        FROM pd_files f
        JOIN pd_clients c ON c.id = f.client_id
        WHERE f.deleted = 0
        ORDER BY f.expired ASC, f.uploaded_at DESC
        LIMIT 200
SQL );
    $file_rows = $s->fetchAll();
    $filter_client = null;
}

include __DIR__ . '/partials/admin-head.php';
?>

<div class="admin-content">

  <div class="section-head">
    <div>
      <h1 style="font-size:clamp(22px,3vw,28px);">
        Files
        <?php if ( $filter_client ) echo '— ' . esc_html( $filter_client['name'] ); ?>
      </h1>
      <p style="color:var(--muted);font-size:14.5px;margin-top:4px;">
        <?php echo count( $file_rows ); ?> file<?php echo count($file_rows) !== 1 ? 's' : ''; ?>
        <?php if ( $filter_client ) echo '<a href="files.php" style="color:var(--accent-deep);font-weight:600;margin-left:12px;">View all →</a>'; ?>
      </p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="togglePanel('upload-panel')">+ Upload file</button>
  </div>

  <?php if ( $error )   echo '<div class="alert alert-error">' . esc_html( $error ) . '</div>'; ?>
  <?php if ( $success ) echo '<div class="alert alert-success">' . esc_html( $success ) . '</div>'; ?>

  <!-- Upload panel -->
  <div id="upload-panel" style="display:none;margin-bottom:28px;">
    <div class="card" style="padding:28px;">
      <h2 style="font-size:18px;margin-bottom:20px;">Upload file to client</h2>
      <form method="post" enctype="multipart/form-data" id="admin-upload-form">
        <input type="hidden" name="_nonce"  value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action"  value="upload">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="field">
            <label for="client_id">Client <span style="color:var(--red)">*</span></label>
            <select id="client_id" name="client_id" required>
              <option value="">— Select client —</option>
              <?php foreach ( $clients as $c ) : ?>
              <option value="<?php echo $c['id']; ?>" <?php selected( $filter_cid, $c['id'] ); ?>>
                <?php echo esc_html( $c['name'] . ' (' . $c['username'] . ')' ); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="label_admin">Label <span style="font-weight:400;color:var(--muted);">(shown to client)</span></label>
            <input id="label_admin" name="label" type="text" placeholder="e.g. Q2 Risk Report">
          </div>
          <div class="field">
            <label for="expires_days">Retention override <span style="font-weight:400;color:var(--muted);">(0 = use client default)</span></label>
            <select id="expires_days" name="expires_days">
              <option value="0">Use client default</option>
              <option value="7">7 days</option>
              <option value="14">14 days</option>
              <option value="30">30 days</option>
              <option value="60">60 days</option>
              <option value="90">90 days</option>
              <option value="365">1 year</option>
            </select>
          </div>
          <div class="field" style="grid-column:1/-1;">
            <label>File <span style="color:var(--red)">*</span></label>
            <div class="drop-zone" id="admin-drop-zone" role="button" tabindex="0" aria-label="Drop file or click to browse" style="padding:28px 20px;">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
              <p><strong>Drop file</strong> or <span style="color:var(--accent-deep);font-weight:600;">click to browse</span></p>
              <p style="font-size:12.5px;margin-top:4px;color:var(--muted);">Max <?php echo PD_MAX_FILE_MB; ?> MB · <?php echo implode(', ', array_map(fn($t)=>'.'.$t, $allowed)); ?></p>
              <p style="font-size:13.5px;margin-top:6px;font-weight:600;" id="admin-selected-file"></p>
              <input type="file" name="file" id="admin-file-input" style="position:absolute;opacity:0;width:1px;height:1px;" aria-label="Select file">
            </div>
          </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px;">
          <button type="submit" class="btn btn-primary btn-sm" id="admin-upload-btn" disabled>Upload to client</button>
          <button type="button" class="btn btn-ghost btn-sm" onclick="togglePanel('upload-panel')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Filter by client -->
  <?php if ( ! $filter_client ) : ?>
  <div style="margin-bottom:16px;">
    <form method="get" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <select name="client" style="padding:8px 12px;border:1px solid var(--line-strong);border-radius:var(--radius-sm);font-family:var(--font-body);font-size:14.5px;">
        <option value="">All clients</option>
        <?php foreach ( $clients as $c ) : ?>
        <option value="<?php echo $c['id']; ?>" <?php selected( $filter_cid, $c['id'] ); ?>><?php echo esc_html( $c['name'] ); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
    </form>
  </div>
  <?php endif; ?>

  <!-- File table -->
  <div class="card" style="overflow:hidden;">
    <?php if ( empty( $file_rows ) ) : ?>
      <div class="empty-state">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
        <p>No files yet.</p>
      </div>
    <?php else : ?>
    <table class="pd-table" aria-label="File list">
      <thead>
        <tr>
          <th>File</th>
          <?php if ( ! $filter_client ) echo '<th>Client</th>'; ?>
          <th>Size</th><th>Uploaded</th><th>Expires</th><th>Status</th><th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $file_rows as $f ) :
          $ext      = strtolower( pathinfo( $f['original_name'], PATHINFO_EXTENSION ) );
          $icon_cls = in_array( $ext, ['jpg','jpeg','png'] ) ? 'img' : $ext;
          $days_left= pd_days_until_expiry( $f['expires_at'] );
          $exp_cls  = $f['expired'] ? 'pill-muted' : ( $days_left > 7 ? 'pill-green' : ( $days_left > 3 ? 'pill-amber' : 'pill-red' ) );
          $exp_txt  = $f['expired'] ? 'Expired' : ( $days_left === 0 ? 'Today' : $days_left . 'd left' );
          $dl_nonce = wp_create_nonce( 'pd_admin_dl_' . $f['id'] );
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:11px;">
              <span class="file-icon <?php echo esc_attr( $icon_cls ); ?>" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
              </span>
              <div>
                <div style="font-weight:600;font-size:14px;"><?php echo esc_html( $f['label'] ?: $f['original_name'] ); ?></div>
                <?php if ( $f['label'] ) : ?><div style="font-size:12px;color:var(--muted);"><?php echo esc_html( $f['original_name'] ); ?></div><?php endif; ?>
              </div>
            </div>
          </td>
          <?php if ( ! $filter_client ) : ?>
          <td style="font-size:13.5px;"><a href="files.php?client=<?php echo $f['client_id']; ?>" style="color:var(--accent-deep);font-weight:600;"><?php echo esc_html( $f['client_name'] ?? '' ); ?></a></td>
          <?php endif; ?>
          <td style="color:var(--muted);font-size:13.5px;"><?php echo pd_format_bytes( $f['size_bytes'] ); ?></td>
          <td style="color:var(--muted);font-size:13.5px;white-space:nowrap;"><?php echo esc_html( gmdate( 'M j, Y', strtotime( $f['uploaded_at'] ) ) ); ?></td>
          <td style="font-size:13.5px;white-space:nowrap;"><?php echo esc_html( gmdate( 'M j, Y', strtotime( $f['expires_at'] ) ) ); ?></td>
          <td><span class="pill <?php echo $exp_cls; ?>"><?php echo esc_html( $exp_txt ); ?></span></td>
          <td style="text-align:right;">
            <a href="files.php?download=<?php echo $f['id']; ?>&_nonce=<?php echo esc_attr( $dl_nonce ); ?>" class="btn btn-ghost btn-sm">Download</a>
            <form method="post" style="display:inline;margin-left:6px;" onsubmit="return confirm('Remove this file from client view?')">
              <input type="hidden" name="_nonce"   value="<?php echo esc_attr( $nonce ); ?>">
              <input type="hidden" name="action"   value="delete">
              <input type="hidden" name="file_id"  value="<?php echo $f['id']; ?>">
              <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red);border-color:var(--red-soft);">Remove</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

</div>

<script>
function togglePanel(id) {
  var el = document.getElementById(id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
(function () {
  var dz  = document.getElementById('admin-drop-zone');
  var fi  = document.getElementById('admin-file-input');
  var btn = document.getElementById('admin-upload-btn');
  var lbl = document.getElementById('admin-selected-file');
  if (!dz) return;
  function setFile(files) {
    if (!files || !files.length) return;
    var dt = new DataTransfer(); dt.items.add(files[0]); fi.files = dt.files;
    lbl.textContent = files[0].name;
    btn.disabled = false;
  }
  dz.addEventListener('click', function() { fi.click(); });
  dz.addEventListener('keydown', function(e) { if (e.key==='Enter'||e.key===' ') fi.click(); });
  fi.addEventListener('change', function(e) { setFile(e.target.files); });
  dz.addEventListener('dragover',  function(e) { e.preventDefault(); dz.classList.add('drag-over'); });
  dz.addEventListener('dragleave', function() { dz.classList.remove('drag-over'); });
  dz.addEventListener('drop', function(e) { e.preventDefault(); dz.classList.remove('drag-over'); setFile(e.dataTransfer.files); });
})();
</script>

<?php include __DIR__ . '/partials/admin-foot.php'; ?>
