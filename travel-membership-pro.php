<?php
/**
 * Plugin Name: Travel Membership Pro
 * Plugin URI: https://nggawe.web.id
 * Description: All-in-one travel membership plugin with tour booking. Track user travel history, manage memberships, and sell tour packages.
 * Version: 2.0.0
 * Author: Kowhi 🦭
 * Author URI: https://nggawe.web.id
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: travel-membership-pro
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('TRAVEL_MEMBERSHIP_PRO_VERSION', '1.0.0');
define('TRAVEL_MEMBERSHIP_PRO_PATH', plugin_dir_path(__FILE__));
define('TRAVEL_MEMBERSHIP_PRO_URL', plugin_dir_url(__FILE__));
define('TRAVEL_MEMBERSHIP_PRO_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
final class Travel_Membership_Pro {
    
    // Single instance
    private static $instance = null;
    
    // Components
    public $post_type;
    public $taxonomy;
    public $tracker;
    public $membership;
    public $admin;
    public $public;
    public $ajax;
    
    /**
     * Get instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_components();
        $this->init_hooks();
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        // Core Travel Features
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'includes/class-travel-post-type.php';
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'includes/class-travel-taxonomy.php';
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'includes/class-user-travel-tracker.php';
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'includes/class-membership-checker.php';
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'includes/class-ajax-handlers.php';
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'includes/class-login-register.php';
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'includes/class-email-notifications.php';
        
        $this->post_type = new TMP_Travel_Post_Type();
        $this->taxonomy = new TMP_Travel_Taxonomy();
        $this->tracker = new TMP_User_Travel_Tracker();
        $this->membership = new TMP_Membership_Checker();
        $this->ajax = new TMP_Ajax_Handlers();
        $this->login = new TMP_Login_Register();
        $this->email = new TMP_Email_Notifications();
        
        // Tour Booking Features
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'includes/class-tour-post-type.php';
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'includes/class-booking-manager.php';
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'includes/class-payment-handler.php';
        
        $this->tour_post_type = new TMP_Tour_Post_Type();
        $this->booking = new TMP_Booking_Manager();
        $this->payment = new TMP_Payment_Handler();
        
        // Admin
        if (is_admin()) {
            require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'admin/class-admin-menu.php';
            require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'admin/class-settings-page.php';
            require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'admin/class-tour-admin.php';
            require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'admin/class-booking-settings.php';
            require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'admin/class-verify-payments.php';
            require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'admin/class-export-bookings.php';
            require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'admin/class-dashboard-widgets.php';
            $this->admin = new TMP_Admin_Menu();
            $this->settings = new TMP_Settings_Page();
            $this->tour_admin = new TMP_Tour_Admin(); // Disabled - settings consolidated
            $this->booking_settings = new TMP_Booking_Settings();
            $this->export = new TMP_Export_Bookings();
            $this->dashboard = new TMP_Dashboard_Widgets();
        }
        
        // Public
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'public/class-shortcodes.php';
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'public/class-asset-loader.php';
        require_once TRAVEL_MEMBERSHIP_PRO_PATH . 'public/class-tour-public.php';
        $this->public = new TMP_Shortcodes();
        $this->assets = new TMP_Asset_Loader();
        $this->tour_public = new TMP_Tour_Public();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_filter('plugin_action_links_' . TRAVEL_MEMBERSHIP_PRO_BASENAME, [$this, 'add_action_links']);
    }
    
    /**
     * Activation hook
     */
    public function activate() {
        // Register post types & taxonomies (flush rewrite rules)
        $this->post_type->register();
        $this->taxonomy->register();
        $this->tour_post_type->register();
        flush_rewrite_rules();
        
        // Create default options
        $default_options = [
            'tmp_free_limit' => 5,
            'tmp_enable_membership' => true,
            'tmp_map_provider' => 'leaflet',
            'tmp_currency' => 'IDR'
        ];
        
        foreach ($default_options as $key => $value) {
            if (!get_option('tmp_' . $key)) {
                add_option('tmp_' . $key, $value);
            }
        }
        
        // Capabilities
        $this->add_capabilities();
    }
    
    /**
     * Deactivation hook
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Load textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'travel-membership-pro',
            false,
            dirname(TRAVEL_MEMBERSHIP_PRO_BASENAME) . '/languages'
        );
    }
    
    /**
     * Add plugin action links
     */
    public function add_action_links($links) {
        $plugin_links = [
            '<a href="' . admin_url('edit.php?post_type=destination') . '">' . __('Destinations', 'travel-membership-pro') . '</a>',
            '<a href="' . admin_url('options-general.php?page=travel-membership-pro') . '">' . __('Settings', 'travel-membership-pro') . '</a>'
        ];
        return array_merge($plugin_links, $links);
    }
    
    /**
     * Add custom capabilities
     */
    private function add_capabilities() {
        $roles = ['administrator', 'editor'];
        $caps = [
            'manage_travel_destinations',
            'edit_travel_destinations',
            'delete_travel_destinations',
            'publish_travel_destinations'
        ];
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($caps as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }
}

/**
 * Initialize plugin
 */
function travel_membership_pro() {
    return Travel_Membership_Pro::instance();
}

// Start plugin
travel_membership_pro();

/**
 * Simple AJAX handler for booking status update (standalone, no class)
 */
add_action('wp_ajax_tmpb_admin_update_status', 'tmpb_simple_status_update');

function tmpb_simple_status_update() {
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $new_status = sanitize_text_field($_POST['status'] ?? '');
    
    if (!$booking_id || !$new_status) {
        wp_send_json_error(['message' => 'Invalid data']);
    }
    
    $valid_statuses = ['pending_payment', 'payment_uploaded', 'paid', 'confirmed', 'cancelled', 'completed', 'refunded'];

}

// Initialize plugin
travel_membership_pro();

// Member Admin Menu
add_action('admin_menu', 'tmpb_register_member_menu', 30);
function tmpb_register_member_menu() {
    add_menu_page('Member Management', '🎫 Member', 'manage_options', 'tmpb-member', 'tmpb_render_member_dashboard', 'dashicons-groups', 31);
    add_submenu_page('tmpb-member', 'Dashboard', '📊 Dashboard', 'manage_options', 'tmpb-member', 'tmpb_render_member_dashboard');
    add_submenu_page('tmpb-member', 'All Members', '👥 All Members', 'manage_options', 'tmpb-member-all', 'tmpb_render_member_all');
    add_submenu_page('tmpb-member', 'Upgrade Payments', '💳 Upgrade Payments', 'manage_options', 'tmpb-member-payments', 'tmpb_render_member_payments');
    add_submenu_page('tmpb-member', 'Spending Reports', '📈 Spending Reports', 'manage_options', 'tmpb-member-reports', 'tmpb_render_member_reports');
}
function tmpb_render_member_dashboard() {
    $file = trailingslashit(get_template_directory()) . 'admin/page-member-dashboard.php';
    if (file_exists($file)) {
        include $file;
    } else {
        echo '<div class="notice notice-error"><p>Member dashboard template not found: <code>' . esc_html($file) . '</code></p></div>';
    }
}

function tmpb_render_member_all() {
    $file = trailingslashit(get_template_directory()) . 'admin/page-member-all.php';
    if (file_exists($file)) {
        include $file;
    } else {
        echo '<div class="notice notice-error"><p>Member list template not found: <code>' . esc_html($file) . '</code></p></div>';
    }
}

function tmpb_render_member_payments() {
    $file = trailingslashit(get_template_directory()) . 'admin/page-member-payments.php';
    if (file_exists($file)) {
        include $file;
    } else {
        echo '<div class="notice notice-error"><p>Member payments template not found: <code>' . esc_html($file) . '</code></p></div>';
    }
}

function tmpb_render_member_reports() {
    $file = trailingslashit(get_template_directory()) . 'admin/page-member-reports.php';
    if (file_exists($file)) {
        include $file;
    } else {
        echo '<div class="notice notice-error"><p>Member reports template not found: <code>' . esc_html($file) . '</code></p></div>';
    }
}
