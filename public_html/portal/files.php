<?php
/**
 * portal.powerdatainc.com/files.php
 * Client file browser — view, download, upload.
 */

define( 'PD_PORTAL', true );
require_once __DIR__ . '/../wp-load.php';
require_once __DIR__ . '/includes/config.php';

$client = pd_require_auth( 'files.php' );

$error   = '';
$success = '';

// ── Handle client-side upload ─────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) ) {
    if ( ! wp_verify_nonce( $_POST['_nonce'] ?? '', 'pd_client_upload' ) ) {
        $error = 'Security check failed. Please try again.';
    } elseif ( $_POST['action'] === 'upload' ) {
        if ( ! empty( $_FILES['file'] ) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE ) {
            $result = pd_upload_file(
                $_FILES['file'],
                $client['id'],
                sanitize_text_field( $_POST['label'] ?? '' ),
                0,
                $client['username']
            );
            if ( $result['ok'] ) {
                $success = 'File uploaded successfully.';
            } else {
                $error = $result['error'];
            }
        }
    }
}

// ── Handle download ───────────────────────────────────────────────────────
if ( isset( $_GET['download'] ) ) {
    if ( wp_verify_nonce( $_GET['_nonce'] ?? '', 'pd_download_' . (int) $_GET['download'] ) ) {
        pd_serve_file( (int) $_GET['download'], $client );
    } else {
        $error = 'Invalid download link. Please try again.';
    }
}

$files = pd_get_client_files( $client['id'] );
$allowed_types = array_keys( unserialize( PD_ALLOWED_TYPES ) );
$nonce_upload  = wp_create_nonce( 'pd_client_upload' );
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Files — <?php echo PD_SITE_NAME; ?> Portal</title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="<?php echo PD_PORTAL_URL; ?>/assets/css/portal.css">
</head>
<body class="pd-page">

<!-- Nav -->
<header class="pd-nav" role="banner">
  <div class="pd-nav-inner">
    <div class="pd-nav-brand">
      <?php echo esc_html( PD_SITE_NAME ); ?>
      <span class="badge">Client Portal</span>
    </div>
    <div class="pd-nav-user">
      <span><?php echo esc_html( $client['name'] ); ?></span>
      <a href="logout.php">Sign out</a>
    </div>
  </div>
</header>

<main class="pd-main" id="main">
  <div class="pd-wrap">

    <!-- Page header -->
    <div class="section-head">
      <div>
        <h1 style="font-size:clamp(22px,3vw,30px);">Your Files</h1>
        <p style="color:var(--muted);font-size:14.5px;margin-top:4px;">Files shared with you by <?php echo esc_html( PD_SITE_NAME ); ?></p>
      </div>
    </div>

    <!-- Alerts -->
    <?php if ( $error )   echo '<div class="alert alert-error"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>' . esc_html( $error ) . '</div>'; ?>
    <?php if ( $success ) echo '<div class="alert alert-success"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>' . esc_html( $success ) . '</div>'; ?>

    <!-- File list -->
    <?php if ( empty( $files ) ) : ?>
      <div class="card">
        <div class="empty-state">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
          <p><strong>No files yet</strong></p>
          <p>Files shared with you will appear here.</p>
        </div>
      </div>
    <?php else : ?>
      <div class="card" style="overflow:hidden;margin-bottom:32px;">
        <table class="pd-table" aria-label="Files shared with you">
          <thead>
            <tr>
              <th scope="col">File</th>
              <th scope="col">Size</th>
              <th scope="col">Uploaded</th>
              <th scope="col">Expires</th>
              <th scope="col" style="text-align:right;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $files as $f ) :
              $ext       = strtolower( pathinfo( $f['original_name'], PATHINFO_EXTENSION ) );
              $icon_cls  = in_array( $ext, ['jpg','jpeg','png'] ) ? 'img' : $ext;
              $days_left = pd_days_until_expiry( $f['expires_at'] );
              $exp_cls   = $days_left > 7 ? 'expiry-ok' : ( $days_left > 3 ? 'expiry-warn' : 'expiry-urgent' );
              $exp_label = $days_left === 0 ? 'Today' : $days_left . 'd left';
              $dl_nonce  = wp_create_nonce( 'pd_download_' . $f['id'] );
            ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:12px;">
                  <span class="file-icon <?php echo esc_attr( $icon_cls ); ?>" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                  </span>
                  <div>
                    <div style="font-weight:600;font-size:14.5px;"><?php echo esc_html( $f['label'] ?: $f['original_name'] ); ?></div>
                    <?php if ( $f['label'] ) : ?><div style="font-size:12.5px;color:var(--muted);"><?php echo esc_html( $f['original_name'] ); ?></div><?php endif; ?>
                  </div>
                </div>
              </td>
              <td style="color:var(--muted);"><?php echo pd_format_bytes( $f['size_bytes'] ); ?></td>
              <td style="color:var(--muted);white-space:nowrap;"><?php echo esc_html( gmdate( 'M j, Y', strtotime( $f['uploaded_at'] ) ) ); ?></td>
              <td><span class="<?php echo $exp_cls; ?>" style="font-size:14px;"><?php echo esc_html( $exp_label ); ?></span></td>
              <td style="text-align:right;">
                <a href="files.php?download=<?php echo $f['id']; ?>&_nonce=<?php echo esc_attr( $dl_nonce ); ?>"
                   class="btn btn-primary btn-sm">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
                  Download
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <!-- Upload section -->
    <div class="card" style="padding:28px 28px 32px;">
      <h2 style="font-size:19px;margin-bottom:6px;">Upload a file</h2>
      <p style="color:var(--muted);font-size:14.5px;margin-bottom:24px;">
        Share a file back with us. Max <?php echo PD_MAX_FILE_MB; ?> MB.
        Allowed types: <?php echo implode( ', ', array_map( fn($t) => '.' . $t, $allowed_types ) ); ?>.
      </p>

      <form method="post" enctype="multipart/form-data" id="upload-form">
        <input type="hidden" name="_nonce"  value="<?php echo esc_attr( $nonce_upload ); ?>">
        <input type="hidden" name="action"  value="upload">

        <div class="drop-zone" id="drop-zone" role="button" tabindex="0" aria-label="Drop files here or click to browse">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
          <p><strong>Drop your file here</strong> or <span style="color:var(--accent-deep);font-weight:600;">click to browse</span></p>
          <p style="font-size:13px;margin-top:4px;" id="selected-file-name"></p>
          <input type="file" name="file" id="file-input" style="position:absolute;opacity:0;width:1px;height:1px;" aria-label="Select file">
        </div>

        <div class="field" style="margin-top:16px;">
          <label for="label">Label <span style="font-weight:400;color:var(--muted);">(optional — helps us identify the file)</span></label>
          <input id="label" name="label" type="text" placeholder="e.g. Signed agreement Q2 2026" maxlength="120">
        </div>

        <button type="submit" class="btn btn-primary btn-sm" style="margin-top:4px;" id="upload-btn" disabled>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/></svg>
          Upload file
        </button>
      </form>
    </div>

  </div>
</main>

<footer class="pd-footer">
  <?php echo esc_html( PD_SITE_NAME ); ?> &#8212; Secure Client Portal &nbsp;&#183;&nbsp; &copy; <?php echo gmdate('Y'); ?>
</footer>

<script>
(function () {
  const dropZone  = document.getElementById('drop-zone');
  const fileInput = document.getElementById('file-input');
  const uploadBtn = document.getElementById('upload-btn');
  const fileLabel = document.getElementById('selected-file-name');

  function setFile(files) {
    if (!files || !files.length) return;
    const dt = new DataTransfer();
    dt.items.add(files[0]);
    fileInput.files = dt.files;
    fileLabel.textContent = files[0].name + ' (' + (files[0].size / 1024).toFixed(1) + ' KB)';
    uploadBtn.disabled = false;
  }

  dropZone.addEventListener('click', () => fileInput.click());
  dropZone.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') fileInput.click(); });
  fileInput.addEventListener('change', e => setFile(e.target.files));

  dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
  dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
  dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    setFile(e.dataTransfer.files);
  });
})();
</script>

</body>
</html>
