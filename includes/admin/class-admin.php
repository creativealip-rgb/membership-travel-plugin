<?php
namespace TravelShip\Admin;

if (!defined('ABSPATH')) exit;

use TravelShip\DB;

class Admin {

    public function init() {
        add_action('admin_menu', [$this, 'add_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_menus() {
        // Main menu
        add_menu_page(
            'TravelShip',
            'TravelShip',
            'manage_options',
            'travelship',
            [new AdminDashboard(), 'render'],
            'dashicons-airplane',
            26
        );

        // Dashboard submenu
        add_submenu_page(
            'travelship',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'travelship',
            [new AdminDashboard(), 'render']
        );

        // Tours submenu
        add_submenu_page(
            'travelship',
            'Kelola Tour',
            'Tours',
            'manage_options',
            'travelship-tours',
            [new AdminTours(), 'render']
        );

        // Bookings submenu
        add_submenu_page(
            'travelship',
            'Kelola Booking',
            'Bookings',
            'manage_options',
            'travelship-bookings',
            [new AdminBookings(), 'render']
        );

        // Members submenu
        add_submenu_page(
            'travelship',
            'Kelola Member',
            'Members',
            'manage_options',
            'travelship-members',
            [new AdminMembers(), 'render']
        );

        // Settings submenu
        add_submenu_page(
            'travelship',
            'Pengaturan',
            'Pengaturan',
            'manage_options',
            'travelship-settings',
            [new AdminSettings(), 'render']
        );
    }

    public function enqueue_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'travelship') === false) return;

        wp_enqueue_style(
            'travelship-admin',
            TRAVELSHIP_PLUGIN_URL . 'assets/css/admin.css',
            [],
            TRAVELSHIP_VERSION
        );

        wp_enqueue_script(
            'travelship-admin',
            TRAVELSHIP_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            TRAVELSHIP_VERSION,
            true
        );

        // Chart.js for dashboard
        if ($hook === 'toplevel_page_travelship') {
            wp_enqueue_script(
                'chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js',
                [],
                '4.0',
                true
            );
        }

        wp_enqueue_media();

        wp_localize_script('travelship-admin', 'travelshipAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('travelship_admin_nonce'),
        ]);
    }
}
