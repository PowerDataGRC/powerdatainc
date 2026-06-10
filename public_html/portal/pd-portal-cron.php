<?php
/**
 * PowerData Portal — pd-portal-cron.php
 *
 * Drop this file in your WordPress theme and require it from functions.php:
 *
 *   require_once get_stylesheet_directory() . '/pd-portal-cron.php';
 *
 * Or paste the contents directly into your theme's functions.php.
 *
 * This file registers a daily WP-Cron job that:
 *   1. Marks expired files (hidden from clients, kept on disk)
 *   2. Sends 3-day expiry warning emails to affected clients
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Register cron schedule ────────────────────────────────────────────────
add_filter( 'cron_schedules', function ( $schedules ) {
    if ( ! isset( $schedules['pd_daily'] ) ) {
        $schedules['pd_daily'] = [
            'interval' => DAY_IN_SECONDS,
            'display'  => 'Once Daily (PowerData Portal)',
        ];
    }
    return $schedules;
} );

// ── Schedule on activation (fires once) ──────────────────────────────────
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'pd_expire_files' ) ) {
        wp_schedule_event( time(), 'pd_daily', 'pd_expire_files' );
    }
} );

// ── The cron callback ─────────────────────────────────────────────────────
add_action( 'pd_expire_files', function () {
    // Load portal includes if not already loaded
    if ( ! defined( 'PD_PORTAL' ) ) {
        define( 'PD_PORTAL', true );
        $portal_includes = WP_CONTENT_DIR . '/pd-portal/includes/config.php';
        if ( file_exists( $portal_includes ) ) {
            require_once $portal_includes;
        } else {
            // Can't proceed without includes
            error_log( '[PowerData Portal] pd-portal/includes/config.php not found. Cron aborted.' );
            return;
        }
    }

    // 1. Send expiry warnings (before marking as expired)
    $expiring = pd_files_expiring_soon( 3 );
    if ( $expiring ) {
        // Group by client
        $by_client = [];
        foreach ( $expiring as $f ) {
            $by_client[ $f['client_id'] ][] = $f;
        }
        foreach ( $by_client as $client_id => $files ) {
            $client = pd_get_client( $client_id );
            if ( $client ) {
                pd_mail_expiry_warning( $client, $files );
                pd_log( 'expiry_warning_sent', count( $files ) . ' files', $client_id, 'cron' );
            }
        }
    }

    // 2. Mark expired files
    $expired_count = pd_expire_due_files();
    if ( $expired_count > 0 ) {
        pd_log( 'cron_expired_files', "$expired_count files marked expired", null, 'cron' );
        error_log( "[PowerData Portal] Cron: $expired_count file(s) marked expired." );
    }
} );

// ── Unschedule on theme deactivation ─────────────────────────────────────
add_action( 'switch_theme', function () {
    $timestamp = wp_next_scheduled( 'pd_expire_files' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'pd_expire_files' );
    }
} );
