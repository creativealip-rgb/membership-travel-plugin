<?php
namespace TravelShip;

if (!defined('ABSPATH')) exit;

/**
 * Main plugin orchestrator — registers all hooks.
 */
class TravelShip {

    public function run() {
        // Admin
        if (is_admin()) {
            $admin = new Admin\Admin();
            $admin->init();
        }

        // Public (shortcodes)
        $public = new Front\PublicHandler();
        $public->init();

        // REST API
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Auto-create member profile on registration
        add_action('user_register', [$this, 'on_user_register']);

        // Add body class for dashboard page
        add_filter('body_class', [$this, 'add_body_class']);
    }

    public function register_rest_routes() {
        $api = new Api\RestApi();
        $api->register_routes();
    }

    public function on_user_register($user_id) {
        $user = get_userdata($user_id);
        if (!$user) return;

        // Add member role
        $user->add_role('travelship_member');

        // Create member profile if not exists
        $existing = DB::get_member_by_user_id($user_id);
        if (!$existing) {
            DB::insert_member([
                'user_id'          => $user_id,
                'membership_level' => 'basic',
                'points'           => 0,
            ]);
        }
    }

    public function add_body_class($classes) {
        $dashboard_page = get_option('travelship_dashboard_page_id');
        if (is_page($dashboard_page)) {
            $classes[] = 'travelship-dashboard-page';
        }
        return $classes;
    }
}
