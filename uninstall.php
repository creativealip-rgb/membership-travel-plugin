<?php
/**
 * TravelShip Uninstall
 * Runs when the plugin is deleted from the WordPress admin.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$prefix = $wpdb->prefix . 'travelship_';

// Drop custom tables
$wpdb->query("DROP TABLE IF EXISTS {$prefix}reviews");
$wpdb->query("DROP TABLE IF EXISTS {$prefix}bookings");
$wpdb->query("DROP TABLE IF EXISTS {$prefix}members");
$wpdb->query("DROP TABLE IF EXISTS {$prefix}tours");

// Remove options
delete_option('travelship_db_version');
delete_option('travelship_settings');
delete_option('travelship_dashboard_page_id');
delete_option('travelship_tour_list_page_id');

// Remove custom role
remove_role('travelship_member');

// Remove capabilities from admin
$admin = get_role('administrator');
if ($admin) {
    $admin->remove_cap('travelship_manage_tours');
    $admin->remove_cap('travelship_manage_bookings');
    $admin->remove_cap('travelship_manage_members');
    $admin->remove_cap('travelship_manage_settings');
}

// Remove user meta
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key = 'travelship_settings'");
