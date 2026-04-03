<?php
/**
 * Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Settings_Page {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    /**
     * Add settings page
     */
    public function add_settings_page() {
        add_options_page(
            __('Travel Membership Pro', 'travel-membership-pro'),
            __('Travel Membership', 'travel-membership-pro'),
            'manage_options',
            'travel-membership-pro',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('tmp_settings_group', 'tmp_free_limit', [
            'type' => 'integer',
            'default' => 5,
            'sanitize_callback' => 'absint',
        ]);
        
        register_setting('tmp_settings_group', 'tmp_enable_membership', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
        
        register_setting('tmp_settings_group', 'tmp_map_provider', [
            'type' => 'string',
            'default' => 'leaflet',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        
        register_setting('tmp_settings_group', 'tmp_map_api_key', [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        
        register_setting('tmp_settings_group', 'tmp_currency', [
            'type' => 'string',
            'default' => 'IDR',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        
        register_setting('tmp_settings_group', 'tmp_upgrade_url', [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Travel Membership Pro Settings', 'travel-membership-pro'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('tmp_settings_group'); ?>
                <?php do_settings_sections('tmp_settings_group'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="tmp_free_limit"><?php esc_html_e('Free Tier Limit', 'travel-membership-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="tmp_free_limit" id="tmp_free_limit" 
                                   value="<?php echo esc_attr(get_option('tmp_free_limit', 5)); ?>" 
                                   class="small-text" min="1" max="1000">
                            <p class="description">
                                <?php esc_html_e('Maximum destinations free users can add', 'travel-membership-pro'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="tmp_enable_membership"><?php esc_html_e('Enable Membership System', 'travel-membership-pro'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="tmp_enable_membership" id="tmp_enable_membership" 
                                   value="1" <?php checked(get_option('tmp_enable_membership', true), true); ?>>
                            <label for="tmp_enable_membership">
                                <?php esc_html_e('Enable membership limits and restrictions', 'travel-membership-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="tmp_map_provider"><?php esc_html_e('Map Provider', 'travel-membership-pro'); ?></label>
                        </th>
                        <td>
                            <select name="tmp_map_provider" id="tmp_map_provider">
                                <option value="leaflet" <?php selected(get_option('tmp_map_provider', 'leaflet'), 'leaflet'); ?>>
                                    <?php esc_html_e('Leaflet (Free, OpenStreetMap)', 'travel-membership-pro'); ?>
                                </option>
                                <option value="google" <?php selected(get_option('tmp_map_provider', 'leaflet'), 'google'); ?>>
                                    <?php esc_html_e('Google Maps', 'travel-membership-pro'); ?>
                                </option>
                                <option value="mapbox" <?php selected(get_option('tmp_map_provider', 'leaflet'), 'mapbox'); ?>>
                                    <?php esc_html_e('Mapbox', 'travel-membership-pro'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="tmp_map_api_key"><?php esc_html_e('Map API Key', 'travel-membership-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="tmp_map_api_key" id="tmp_map_api_key" 
                                   value="<?php echo esc_attr(get_option('tmp_map_api_key', '')); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Required for Google Maps or Mapbox', 'travel-membership-pro'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="tmp_currency"><?php esc_html_e('Currency', 'travel-membership-pro'); ?></label>
                        </th>
                        <td>
                            <select name="tmp_currency" id="tmp_currency">
                                <option value="IDR" <?php selected(get_option('tmp_currency', 'IDR'), 'IDR'); ?>>
                                    IDR - Indonesian Rupiah
                                </option>
                                <option value="USD" <?php selected(get_option('tmp_currency', 'IDR'), 'USD'); ?>>
                                    USD - US Dollar
                                </option>
                                <option value="EUR" <?php selected(get_option('tmp_currency', 'IDR'), 'EUR'); ?>>
                                    EUR - Euro
                                </option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="tmp_upgrade_url"><?php esc_html_e('Upgrade Page URL', 'travel-membership-pro'); ?></label>
                        </th>
                        <td>
                            <input type="url" name="tmp_upgrade_url" id="tmp_upgrade_url" 
                                   value="<?php echo esc_attr(get_option('tmp_upgrade_url', '')); ?>" 
                                   class="large-text"
                                   placeholder="https://yoursite.com/membership/">
                            <p class="description">
                                <?php esc_html_e('URL where users can upgrade their membership', 'travel-membership-pro'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2><?php esc_html_e('Shortcodes', 'travel-membership-pro'); ?></h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Shortcode', 'travel-membership-pro'); ?></th>
                        <th><?php esc_html_e('Description', 'travel-membership-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[travel_dashboard]</code></td>
                        <td><?php esc_html_e('Display user travel dashboard with form and history', 'travel-membership-pro'); ?></td>
                    </tr>
                    <tr>
                        <td><code>[travel_map]</code></td>
                        <td><?php esc_html_e('Display interactive travel map', 'travel-membership-pro'); ?></td>
                    </tr>
                    <tr>
                        <td><code>[travel_stats]</code></td>
                        <td><?php esc_html_e('Display user travel statistics', 'travel-membership-pro'); ?></td>
                    </tr>
                    <tr>
                        <td><code>[user_travel_history]</code></td>
                        <td><?php esc_html_e('Display user travel history list', 'travel-membership-pro'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
}
