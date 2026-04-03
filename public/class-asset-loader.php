<?php
/**
 * Asset Loader
 * Load CSS and JS files
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Asset_Loader {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // CSS
        wp_enqueue_style(
            'tmp-frontend-css',
            TRAVEL_MEMBERSHIP_PRO_URL . 'public/css/travel-membership.css',
            [],
            TRAVEL_MEMBERSHIP_PRO_VERSION
        );
        
        // Leaflet CSS (for map)
        wp_enqueue_style(
            'leaflet-css',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            [],
            '1.9.4'
        );
        
        // JS
        wp_enqueue_script(
            'tmp-frontend-js',
            TRAVEL_MEMBERSHIP_PRO_URL . 'public/js/travel-membership.js',
            ['jquery'],
            TRAVEL_MEMBERSHIP_PRO_VERSION,
            true
        );
        
        // Leaflet JS
        wp_enqueue_script(
            'leaflet-js',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            [],
            '1.9.4',
            true
        );
        
        // Localize script
        wp_localize_script('tmp-frontend-js', 'tmpAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tmp_frontend_nonce'),
            'userNonce' => wp_create_nonce('tmp_user_nonce'),
            'publicNonce' => wp_create_nonce('tmp_public_nonce'),
            'uploadNonce' => wp_create_nonce('tmp_upload_nonce'),
            'isUserLoggedIn' => is_user_logged_in(),
            'currentUserId' => get_current_user_id(),
            'i18n' => [
                'addTravel' => __('Add Travel', 'travel-membership-pro'),
                'removeTravel' => __('Remove Travel', 'travel-membership-pro'),
                'loading' => __('Loading...', 'travel-membership-pro'),
                'success' => __('Success!', 'travel-membership-pro'),
                'error' => __('Error', 'travel-membership-pro'),
                'confirmRemove' => __('Are you sure you want to remove this travel?', 'travel-membership-pro'),
            ],
        ]);
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'tmp-') === false && $hook !== 'edit.php?post_type=destination') {
            return;
        }
        
        wp_enqueue_style(
            'tmp-admin-css',
            TRAVEL_MEMBERSHIP_PRO_URL . 'admin/css/admin.css',
            [],
            TRAVEL_MEMBERSHIP_PRO_VERSION
        );
        
        wp_enqueue_script(
            'tmp-admin-js',
            TRAVEL_MEMBERSHIP_PRO_URL . 'admin/js/admin.js',
            ['jquery'],
            TRAVEL_MEMBERSHIP_PRO_VERSION,
            true
        );
    }
}
