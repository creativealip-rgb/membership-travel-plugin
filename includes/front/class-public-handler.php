<?php
namespace TravelShip\Front;

if (!defined('ABSPATH')) exit;

class PublicHandler {

    public function init() {
        add_shortcode('travelship_dashboard', [$this, 'render_dashboard']);
        add_shortcode('travelship_tours', [$this, 'render_tours']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('template_redirect', [$this, 'protect_dashboard']);
    }

    /**
     * Protect dashboard page — redirect to login if not authenticated.
     */
    public function protect_dashboard() {
        $dashboard_page = get_option('travelship_dashboard_page_id');
        if ($dashboard_page && is_page($dashboard_page) && !is_user_logged_in()) {
            wp_redirect(wp_login_url(get_permalink($dashboard_page)));
            exit;
        }
    }

    /**
     * Enqueue React app assets on dashboard and tour pages.
     */
    public function enqueue_assets() {
        $dashboard_page = get_option('travelship_dashboard_page_id');
        $tour_list_page = get_option('travelship_tour_list_page_id');
        $is_ts_page = (is_page($dashboard_page) || is_page($tour_list_page));

        if (!$is_ts_page) return;

        // Load the built React app
        $manifest_path = TRAVELSHIP_PLUGIN_DIR . 'frontend/dist/.vite/manifest.json';
        if (file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true);
            $entry = $manifest['index.html'] ?? null;

            if ($entry) {
                // CSS
                if (!empty($entry['css'])) {
                    foreach ($entry['css'] as $i => $css_file) {
                        wp_enqueue_style(
                            'travelship-react-' . $i,
                            TRAVELSHIP_PLUGIN_URL . 'frontend/dist/' . $css_file,
                            [],
                            TRAVELSHIP_VERSION
                        );
                    }
                }

                // JS
                wp_enqueue_script(
                    'travelship-react',
                    TRAVELSHIP_PLUGIN_URL . 'frontend/dist/' . $entry['file'],
                    [],
                    TRAVELSHIP_VERSION,
                    true
                );
            }
        } else {
            // Dev mode — load from Vite dev server
            wp_enqueue_script(
                'travelship-vite-client',
                'http://localhost:5173/@vite/client',
                [],
                null,
                false
            );
            wp_enqueue_script(
                'travelship-react-dev',
                'http://localhost:5173/src/main.jsx',
                [],
                null,
                true
            );
        }

        // Pass data to React
        wp_localize_script(
            file_exists($manifest_path) ? 'travelship-react' : 'travelship-react-dev',
            'travelshipData',
            [
                'api_url'       => rest_url('travelship/v1'),
                'nonce'         => wp_create_nonce('wp_rest'),
                'is_logged_in'  => is_user_logged_in(),
                'dashboard_url' => get_permalink($dashboard_page),
                'tours_url'     => get_permalink($tour_list_page),
                'home_url'      => home_url(),
                'plugin_url'    => TRAVELSHIP_PLUGIN_URL,
                'bank_info'     => [
                    'name'    => \TravelShip\Admin\AdminSettings::get('bank_name'),
                    'account' => \TravelShip\Admin\AdminSettings::get('bank_account'),
                    'holder'  => \TravelShip\Admin\AdminSettings::get('bank_holder'),
                ],
            ]
        );
    }

    /**
     * Dashboard shortcode — renders mount point for React app.
     */
    public function render_dashboard() {
        if (!is_user_logged_in()) {
            return '<p>Silakan <a href="' . wp_login_url(get_permalink()) . '">login</a> untuk mengakses dashboard.</p>';
        }
        return '<div id="travelship-app" data-page="dashboard"></div>';
    }

    /**
     * Tours shortcode — renders mount point for React tour listing.
     */
    public function render_tours() {
        return '<div id="travelship-app" data-page="tours"></div>';
    }
}
