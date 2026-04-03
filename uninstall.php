<?php
/**
 * Uninstall Travel Membership Pro
 * 
 * This file runs when the plugin is deleted from WordPress.
 * It cleans up all plugin data from the database.
 */

// Exit if not called from WordPress uninstall
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('tmp_free_limit');
delete_option('tmp_enable_membership');
delete_option('tmp_map_provider');
delete_option('tmp_map_api_key');
delete_option('tmp_currency');
delete_option('tmp_upgrade_url');

// Delete all destinations (custom post type)
$posts = get_posts([
    'post_type' => 'destination',
    'numberposts' => -1,
    'post_status' => 'any',
]);

foreach ($posts as $post) {
    wp_delete_post($post->ID, true);
}

// Delete user meta for all users
global $wpdb;

$wpdb->query(
    "DELETE FROM $wpdb->usermeta 
    WHERE meta_key = '_visited_destinations'"
);

// Clear any transients
delete_transient('tmp_destinations_cache');
delete_transient('tmp_countries_cache');

// Remove capabilities from roles
$roles = ['administrator', 'editor'];
$caps = [
    'manage_travel_destinations',
    'edit_travel_destinations',
    'delete_travel_destinations',
    'publish_travel_destinations',
];

foreach ($roles as $role_name) {
    $role = get_role($role_name);
    if ($role) {
        foreach ($caps as $cap) {
            $role->remove_cap($cap);
        }
    }
}

// Unregister taxonomies (WordPress handles this automatically)
// But we can clean up terms if needed
// Uncomment if you want to delete all terms:
/*
$taxonomies = ['country', 'travel_category'];
foreach ($taxonomies as $taxonomy) {
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ]);
    
    foreach ($terms as $term) {
        wp_delete_term($term->term_id, $taxonomy);
    }
}
*/

// Plugin data completely removed
// WordPress will automatically remove the plugin folder
