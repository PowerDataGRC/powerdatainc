<?php if ( ! defined( 'PD_PORTAL' ) ) exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo isset( $page_title ) ? esc_html( $page_title ) . ' — ' : ''; ?>Admin — <?php echo PD_SITE_NAME; ?></title>
  <meta name="robots" content="noindex,nofollow">
  <link rel="stylesheet" href="<?php echo PD_PORTAL_URL; ?>/assets/css/portal.css">
</head>
<body>
<div class="admin-shell">

  <!-- Sidebar -->
  <aside class="admin-sidebar" role="navigation" aria-label="Admin navigation">
    <div class="admin-sidebar-brand">
      <h1><?php echo esc_html( PD_SITE_NAME ); ?></h1>
      <p>Admin Portal</p>
    </div>
    <?php
    $current = basename( $_SERVER['PHP_SELF'] );
    function pd_nav_link( string $href, string $label, string $icon_path, string $current ): void {
        $file   = basename( $href );
        $active = $current === $file ? ' active' : '';
        echo '<a href="' . esc_url( $href ) . '" class="' . ltrim( $active ) . '">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">' . $icon_path . '</svg>';
        echo esc_html( $label ) . '</a>';
    }
    ?>
    <nav class="admin-nav">
      <div class="nav-section">Overview</div>
      <?php pd_nav_link( 'index.php',   'Dashboard', '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>', $current ); ?>

      <div class="nav-section">Clients</div>
      <?php pd_nav_link( 'clients.php', 'Clients',   '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', $current ); ?>
      <?php pd_nav_link( 'files.php',   'Files',     '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>', $current ); ?>

      <div class="nav-section">Settings</div>
      <?php pd_nav_link( 'settings.php','Settings',  '<circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 0 0 4.93 19.07M4.93 4.93A10 10 0 0 0 19.07 19.07"/>', $current ); ?>
    </nav>

    <!-- Sidebar footer -->
    <div style="padding:16px 20px;border-top:1px solid rgba(255,255,255,.08);font-size:12.5px;color:#4A5C72;">
      <?php
      $wp_user = wp_get_current_user();
      echo esc_html( $wp_user->display_name ?? 'Admin' );
      ?>
      &nbsp;·&nbsp;
      <a href="<?php echo esc_url( wp_logout_url( PD_ADMIN_URL ) ); ?>" style="color:#4A5C72;text-decoration:underline;">Sign out</a>
    </div>
  </aside>
