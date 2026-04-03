<?php
/**
 * Shortcodes
 * All frontend shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Shortcodes {
    
    public function __construct() {
        add_shortcode('travel_dashboard', [$this, 'travel_dashboard']);
        add_shortcode('travel_map', [$this, 'travel_map']);
        add_shortcode('travel_stats', [$this, 'travel_stats']);
        add_shortcode('user_travel_history', [$this, 'user_travel_history']);
    }
    
    /**
     * Travel Dashboard Shortcode
     * [travel_dashboard]
     */
    public function travel_dashboard($atts) {
        $atts = shortcode_atts([], $atts);
        
        if (!is_user_logged_in()) {
            return '<div class="tmp-dashboard-notice">' . 
                   __('Please login to access your travel dashboard', 'travel-membership-pro') . 
                   ' ' . wp_login_url(get_permalink()) . 
                   '</div>';
        }
        
        ob_start();
        include TRAVEL_MEMBERSHIP_PRO_PATH . 'templates/travel-dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * Travel Map Shortcode
     * [travel_map user_id="123" height="500"]
     */
    public function travel_map($atts) {
        $atts = shortcode_atts([
            'user_id' => get_current_user_id(),
            'height' => '500',
            'width' => '100%',
            'zoom' => '2',
            'show_all' => false,
        ], $atts);
        
        ob_start();
        include TRAVEL_MEMBERSHIP_PRO_PATH . 'templates/travel-map.php';
        return ob_get_clean();
    }
    
    /**
     * Travel Stats Shortcode
     * [travel_stats user_id="123"]
     */
    public function travel_stats($atts) {
        $atts = shortcode_atts([
            'user_id' => get_current_user_id(),
        ], $atts);
        
        $user_id = absint($atts['user_id']);
        
        if (!$user_id) {
            return '';
        }
        
        $tracker = new TMP_User_Travel_Tracker();
        $stats = $tracker->get_stats($user_id);
        
        ob_start();
        include TRAVEL_MEMBERSHIP_PRO_PATH . 'templates/travel-stats.php';
        return ob_get_clean();
    }
    
    /**
     * User Travel History Shortcode
     * [user_travel_history limit="10"]
     */
    public function user_travel_history($atts) {
        $atts = shortcode_atts([
            'user_id' => get_current_user_id(),
            'limit' => '10',
        ], $atts);
        
        $user_id = absint($atts['user_id']);
        $limit = absint($atts['limit']);
        
        if (!$user_id) {
            return '';
        }
        
        $tracker = new TMP_User_Travel_Tracker();
        $travels = $tracker->get_travels($user_id);
        
        // Limit results
        if ($limit > 0) {
            $travels = array_slice($travels, 0, $limit);
        }
        
        ob_start();
        include TRAVEL_MEMBERSHIP_PRO_PATH . 'templates/travel-history.php';
        return ob_get_clean();
    }
}
