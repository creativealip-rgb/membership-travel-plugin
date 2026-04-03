<?php
/**
 * Membership Checker
 * Handles membership validation and limits
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Membership_Checker {
    
    /**
     * Check if user can add destination
     * 
     * @param int $user_id User ID
     * @return bool|WP_Error True if can add, error otherwise
     */
    public function can_add_destination($user_id) {
        $user_id = absint($user_id);
        
        if (!get_option('tmp_enable_membership', true)) {
            return true; // Membership disabled, unlimited
        }
        
        $membership_status = $this->get_membership_status($user_id);
        
        // Free tier
        if ($membership_status === 'free' || $membership_status === 'none') {
            $limit = absint(get_option('tmp_free_limit', 5));
            $tracker = new TMP_User_Travel_Tracker();
            $current_count = $tracker->get_travel_count($user_id);
            
            if ($current_count >= $limit) {
                return new WP_Error(
                    'limit_reached',
                    sprintf(
                        __('You have reached your travel limit (%d destinations). Upgrade to add more!', 'travel-membership-pro'),
                        $limit
                    )
                );
            }
        }
        
        // Paid tiers (integrate with actual membership plugin)
        if ($membership_status === 'basic' || $membership_status === 'premium') {
            return true; // Unlimited for paid members
        }
        
        return true;
    }
    
    /**
     * Get user membership status
     * 
     * @param int $user_id User ID
     * @return string Membership status: none, free, basic, premium
     */
    public function get_membership_status($user_id) {
        $user_id = absint($user_id);
        
        // Check for popular membership plugins
        if (class_exists('PMPro_MembershipLevel')) {
            // Paid Memberships Pro
            $level = pmpro_getMembershipLevelForUser($user_id);
            if ($level) {
                return $level->name === 'Free' ? 'free' : 'paid';
            }
            return 'none';
        }
        
        if (class_exists('MemberPress_Registration')) {
            // MemberPress
            $meals = MeprProduct::find_all_with_access($user_id);
            if (!empty($meals)) {
                return 'paid';
            }
            return 'none';
        }
        
        if (class_exists('WC_Memberships_User_Memberships')) {
            // WooCommerce Memberships
            $memberships = wc_memberships_get_user_active_memberships($user_id);
            if (!empty($memberships)) {
                return 'paid';
            }
            return 'none';
        }
        
        // Default: check user meta
        $status = get_user_meta($user_id, '_tmp_membership_status', true);
        return $status ?: 'free';
    }
    
    /**
     * Get membership limit
     * 
     * @param int $user_id User ID
     * @return int Destination limit
     */
    public function get_destination_limit($user_id) {
        $status = $this->get_membership_status($user_id);
        
        switch ($status) {
            case 'basic':
                return apply_filters('tmp_basic_limit', 50);
            case 'premium':
                return apply_filters('tmp_premium_limit', -1); // Unlimited
            default:
                return absint(get_option('tmp_free_limit', 5));
        }
    }
    
    /**
     * Check if user can access feature
     * 
     * @param string $feature Feature name
     * @param int $user_id User ID
     * @return bool
     */
    public function can_access_feature($feature, $user_id) {
        $status = $this->get_membership_status($user_id);
        
        $features = [
            'travel_map' => ['free', 'basic', 'premium'],
            'travel_export' => ['basic', 'premium'],
            'bulk_upload' => ['premium'],
            'advanced_stats' => ['basic', 'premium'],
            'custom_badges' => ['premium'],
        ];
        
        if (!isset($features[$feature])) {
            return true;
        }
        
        return in_array($status, $features[$feature]);
    }
    
    /**
     * Get upgrade URL
     * 
     * @return string Upgrade page URL
     */
    public function get_upgrade_url() {
        // Check for membership plugin pages
        if (function_exists('wc_get_page_permalink')) {
            $page_id = wc_get_page_id('membership');
            if ($page_id) {
                return get_permalink($page_id);
            }
        }
        
        // Default: settings page or custom
        return get_option('tmp_upgrade_url', home_url('/membership/'));
    }
    
    /**
     * Display upgrade notice
     * 
     * @param string $message Message to display
     */
    public function display_upgrade_notice($message = '') {
        if (empty($message)) {
            $message = __('Upgrade your membership to unlock more features!', 'travel-membership-pro');
        }
        
        $upgrade_url = $this->get_upgrade_url();
        
        echo '<div class="tmp-upgrade-notice notice-warning">';
        echo '<p>' . esc_html($message) . ' ';
        echo '<a href="' . esc_url($upgrade_url) . '" class="button button-primary">';
        echo __('Upgrade Now', 'travel-membership-pro');
        echo '</a></p>';
        echo '</div>';
    }
}
