<?php
/**
 * admin.powerdatainc.com/clients.php
 * Create, edit, enable/disable clients and reset passwords.
 */

define( 'PD_PORTAL', true );
require_once __DIR__ . '/../../wp-load.php';
require_once dirname( __DIR__ ) . '/includes/config.php';

pd_admin_require();

$error   = '';
$success = '';
$editing = null;

// ── Handle POST actions ───────────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) ) {
    if ( ! wp_verify_nonce( $_POST['_nonce'] ?? '', 'pd_admin_clients' ) ) {
        $error = 'Security check failed.';
    } else {
        switch ( $_POST['action'] ) {

            case 'create_client':
                $username = sanitize_text_field( $_POST['username'] ?? '' );
                $email    = sanitize_email( $_POST['email'] ?? '' );
                $password = $_POST['password'] ?? '';

                if ( ! $username || ! $email || strlen( $password ) < 8 ) {
                    $error = 'Username, email, and a password of at least 8 characters are required.';
                } elseif ( pd_get_client_by_username( $username ) ) {
                    $error = 'That username is already taken.';
                } else {
                    $id = pd_create_client( [
                        'name'           => sanitize_text_field( $_POST['name'] ?? $username ),
                        'email'          => $email,
                        'username'       => $username,
                        'password'       => $password,
                        'retention_days' => (int) ( $_POST['retention_days'] ?? 30 ),
                        'notes'          => sanitize_textarea_field( $_POST['notes'] ?? '' ),
                    ] );
                    pd_log( 'client_created', "username=$username", $id, 'admin' );
                    $success = 'Client created. Username: ' . $username;
                }
                break;

            case 'update_client':
                $id   = (int) ( $_POST['client_id'] ?? 0 );
                $data = [
                    'name'           => sanitize_text_field( $_POST['name'] ?? '' ),
                    'email'          => sanitize_email( $_POST['email'] ?? '' ),
                    'retention_days' => (int) ( $_POST['retention_days'] ?? 30 ),
                    'notes'          => sanitize_textarea_field( $_POST['notes'] ?? '' ),
                    'active'         => isset( $_POST['active'] ) ? 1 : 0,
                ];
                if ( ! empty( $_POST['password'] ) ) {
                    if ( strlen( $_POST['password'] ) < 8 ) {
                        $error = 'New password must be at least 8 characters.';
                        break;
                    }
                    $data['password'] = $_POST['password'];
                }
                pd_update_client( $id, $data );
                pd_log( 'client_updated', '', $id, 'admin' );
                $success = 'Client updated.';
                break;

            case 'send_magic_link':
                $id     = (int) ( $_POST['client_id'] ?? 0 );
                $client = pd_get_client( $id );
                if ( $client ) {
                    pd_mail_magic_link( $client );
                    $success = 'Magic sign-in link sent to ' . $client['email'];
                    pd_log( 'magic_link_sent_by_admin', '', $id, 'admin' );
                }
                break;
        }
    }
}

// ── Load editing client ───────────────────────────────────────────────────
if ( isset( $_GET['edit'] ) ) {
    $editing = pd_get_client( (int) $_GET['edit'] );
}

$clients = pd_get_all_clients();
$nonce   = wp_create_nonce( 'pd_admin_clients' );

include __DIR__ . '/partials/admin-head.php';
?>

<div class="admin-content">

  <div class="section-head">
    <div>
      <h1 style="font-size:clamp(22px,3vw,28px);">Clients</h1>
      <p style="color:var(--muted);font-size:14.5px;margin-top:4px;"><?php echo count( $clients ); ?> total</p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="togglePanel('new-client-panel')">+ New client</button>
  </div>

  <?php if ( $error )   echo '<div class="alert alert-error"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>' . esc_html( $error ) . '</div>'; ?>
  <?php if ( $success ) echo '<div class="alert alert-success"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>' . esc_html( $success ) . '</div>'; ?>

  <!-- New Client Panel -->
  <div id="new-client-panel" style="display:none;margin-bottom:28px;">
    <div class="card" style="padding:28px;">
      <h2 style="font-size:18px;margin-bottom:20px;">New client</h2>
      <form method="post">
        <input type="hidden" name="_nonce"  value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action"  value="create_client">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="field"><label>Full name</label><input name="name" type="text" placeholder="Jane Smith" required></div>
          <div class="field"><label>Email</label><input name="email" type="email" placeholder="jane@company.com" required></div>
          <div class="field"><label>Username</label><input name="username" type="text" placeholder="janesmith" required autocomplete="off"></div>
          <div class="field"><label>Password (min 8 chars)</label><input name="password" type="password" minlength="8" required autocomplete="new-password"></div>
          <div class="field">
            <label>Default file retention</label>
            <select name="retention_days">
              <option value="30">30 days</option>
              <option value="60">60 days</option>
              <option value="90">90 days</option>
              <option value="365">1 year</option>
            </select>
          </div>
          <div class="field" style="grid-column:1/-1;"><label>Notes (internal)</label><textarea name="notes" rows="2" placeholder="Optional — client company, project, etc."></textarea></div>
        </div>
        <div style="display:flex;gap:10px;margin-top:4px;">
          <button type="submit" class="btn btn-primary btn-sm">Create client</button>
          <button type="button" class="btn btn-ghost btn-sm" onclick="togglePanel('new-client-panel')">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Client Panel -->
  <?php if ( $editing ) : ?>
  <div style="margin-bottom:28px;">
    <div class="card" style="padding:28px;border-color:var(--accent);">
      <h2 style="font-size:18px;margin-bottom:20px;">Edit client — <?php echo esc_html( $editing['name'] ); ?></h2>
      <form method="post">
        <input type="hidden" name="_nonce"     value="<?php echo esc_attr( $nonce ); ?>">
        <input type="hidden" name="action"     value="update_client">
        <input type="hidden" name="client_id"  value="<?php echo $editing['id']; ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="field"><label>Full name</label><input name="name"  type="text"  value="<?php echo esc_attr( $editing['name'] ); ?>"  required></div>
          <div class="field"><label>Email</label><input     name="email" type="email" value="<?php echo esc_attr( $editing['email'] ); ?>" required></div>
          <div class="field"><label>New password <span style="font-weight:400;color:var(--muted);">(leave blank to keep)</span></label><input name="password" type="password" minlength="8" autocomplete="new-password" placeholder="••••••••"></div>
          <div class="field">
            <label>File retention</label>
            <select name="retention_days">
              <?php foreach ( [30,60,90,365] as $d ) : ?>
              <option value="<?php echo $d; ?>" <?php selected( $editing['retention_days'], $d ); ?>><?php echo $d; ?> days</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field" style="grid-column:1/-1;"><label>Notes</label><textarea name="notes" rows="2"><?php echo esc_textarea( $editing['notes'] ?? '' ); ?></textarea></div>
          <div class="field" style="grid-column:1/-1;">
            <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
              <input type="checkbox" name="active" <?php checked( $editing['active'], 1 ); ?> style="width:auto;margin:0;">
              Account active
            </label>
          </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:4px;">
          <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
          <a href="clients.php" class="btn btn-ghost btn-sm">Cancel</a>
          <!-- Send magic link -->
          <form method="post" style="margin:0;">
            <input type="hidden" name="_nonce"    value="<?php echo esc_attr( $nonce ); ?>">
            <input type="hidden" name="action"    value="send_magic_link">
            <input type="hidden" name="client_id" value="<?php echo $editing['id']; ?>">
            <button type="submit" class="btn btn-ghost btn-sm">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/></svg>
              Send sign-in link
            </button>
          </form>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- Client list -->
  <div class="card" style="overflow:hidden;">
    <?php if ( empty( $clients ) ) : ?>
      <div class="empty-state"><p>No clients yet. Create one above.</p></div>
    <?php else : ?>
    <table class="pd-table" aria-label="Client list">
      <thead>
        <tr>
          <th>Name</th><th>Username</th><th>Email</th><th>Retention</th><th>Status</th><th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $clients as $c ) :
          $file_count = (int) pd_db()->prepare( 'SELECT COUNT(*) FROM pd_files WHERE client_id = ? AND deleted = 0 AND expired = 0' )->execute([$c['id']]) ? pd_db()->prepare( 'SELECT COUNT(*) FROM pd_files WHERE client_id = ? AND deleted = 0 AND expired = 0' )->execute([$c['id']]) && ($cnt = pd_db()->prepare( 'SELECT COUNT(*) FROM pd_files WHERE client_id = ? AND deleted = 0 AND expired = 0' )) && $cnt->execute([$c['id']]) ? (int)$cnt->fetchColumn() : 0 : 0;
          // Simpler:
          $fc_stmt = pd_db()->prepare( 'SELECT COUNT(*) FROM pd_files WHERE client_id = ? AND deleted = 0 AND expired = 0' );
          $fc_stmt->execute( [$c['id']] );
          $file_count = (int) $fc_stmt->fetchColumn();
        ?>
        <tr>
          <td>
            <div style="font-weight:600;"><?php echo esc_html( $c['name'] ); ?></div>
            <div style="font-size:12.5px;color:var(--muted);"><?php echo $file_count; ?> active file<?php echo $file_count !== 1 ? 's' : ''; ?></div>
          </td>
          <td style="font-family:monospace;font-size:14px;"><?php echo esc_html( $c['username'] ); ?></td>
          <td style="color:var(--muted);font-size:14px;"><?php echo esc_html( $c['email'] ); ?></td>
          <td style="color:var(--muted);font-size:14px;"><?php echo $c['retention_days']; ?>d</td>
          <td><span class="pill <?php echo $c['active'] ? 'pill-green' : 'pill-muted'; ?>"><?php echo $c['active'] ? 'Active' : 'Disabled'; ?></span></td>
          <td style="text-align:right;">
            <a href="clients.php?edit=<?php echo $c['id']; ?>" class="btn btn-ghost btn-sm">Edit</a>
            <a href="files.php?client=<?php echo $c['id']; ?>"  class="btn btn-ghost btn-sm" style="margin-left:6px;">Files</a>
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
  const el = document.getElementById(id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php include __DIR__ . '/partials/admin-foot.php'; ?>
