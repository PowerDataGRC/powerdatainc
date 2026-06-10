<?php
define( 'PD_PORTAL', true );
require_once __DIR__ . '/../wp-load.php';
require_once __DIR__ . '/includes/config.php';
pd_session_start();
pd_logout();
header( 'Location: ' . PD_PORTAL_URL . '/login.php' );
exit;
