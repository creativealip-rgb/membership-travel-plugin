<?php
/**
 * Admin Menu
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Admin_Menu {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menus']);
    }
    
    /**
     * Add admin menus
     */
    public function add_menus() {
        // Main menu (already added by post type)
        
        // All Travels submenu
        add_submenu_page(
            'edit.php?post_type=destination',
            __('All Member Travels', 'travel-membership-pro'),
            __('Member Travels', 'travel-membership-pro'),
            'manage_options',
            'tmp-all-travels',
            [$this, 'render_all_travels_page']
        );
        
        // Reports submenu
        add_submenu_page(
            'edit.php?post_type=destination',
            __('Reports', 'travel-membership-pro'),
            __('Reports', 'travel-membership-pro'),
            'manage_options',
            'tmp-reports',
            [$this, 'render_reports_page']
        );
    }
    
    /**
     * Render All Travels page
     */
    public function render_all_travels_page() {
        include TRAVEL_MEMBERSHIP_PRO_PATH . 'admin/views/all-travels.php';
    }
    
    /**
     * Render Reports page
     */
    public function render_reports_page() {
        include TRAVEL_MEMBERSHIP_PRO_PATH . 'admin/views/reports.php';
    }
}
