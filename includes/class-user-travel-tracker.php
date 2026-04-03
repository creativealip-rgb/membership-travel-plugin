<?php
/**
 * User Travel Tracker
 * Handles all user travel history operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_User_Travel_Tracker {
    
    /**
     * Meta key for storing user travels
     */
    const META_KEY = '_visited_destinations';
    
    public function __construct() {
        // Actions
        add_action('wp_ajax_tmp_add_travel', [$this, 'ajax_add_travel']);
        add_action('wp_ajax_tmp_remove_travel', [$this, 'ajax_remove_travel']);
    }
    
    /**
     * Add travel to user history
     * 
     * @param int $user_id User ID
     * @param array $data Travel data
     * @return int|WP_Error Destination post ID or error
     */
    public function add_travel($user_id, $data) {
        $user_id = absint($user_id);
        if (!$user_id) {
            return new WP_Error('invalid_user', __('Invalid user ID', 'travel-membership-pro'));
        }
        
        // Check membership limit
        $membership_checker = new TMP_Membership_Checker();
        $can_add = $membership_checker->can_add_destination($user_id);
        
        if (is_wp_error($can_add)) {
            return $can_add;
        }
        
        // Create destination post
        $destination_id = $this->create_destination($data);
        
        if (is_wp_error($destination_id)) {
            return $destination_id;
        }
        
        // Get existing travels
        $travels = $this->get_travels($user_id);
        
        // Add new travel
        $travels[] = [
            'destination_id' => $destination_id,
            'visit_date' => sanitize_text_field($data['visit_date']),
            'notes' => sanitize_textarea_field($data['notes'] ?? ''),
            'photos' => array_map('absint', $data['photos'] ?? []),
            'rating' => absint($data['rating'] ?? 0),
            'added_at' => current_time('mysql'),
        ];
        
        // Update user meta
        update_user_meta($user_id, self::META_KEY, $travels);
        
        // Trigger action
        do_action('tmp_travel_added', $user_id, $destination_id, $travels);
        
        return $destination_id;
    }
    
    /**
     * Create destination post
     */
    private function create_destination($data) {
        $post_data = [
            'post_title' => sanitize_text_field($data['title']),
            'post_content' => sanitize_textarea_field($data['description'] ?? ''),
            'post_status' => 'publish',
            'post_type' => 'destination',
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Set thumbnail
        if (!empty($data['thumbnail_id'])) {
            set_post_thumbnail($post_id, absint($data['thumbnail_id']));
        }
        
        // Set country taxonomy
        if (!empty($data['country_id'])) {
            wp_set_object_terms($post_id, absint($data['country_id']), 'country');
        }
        
        // Set category taxonomy
        if (!empty($data['category_ids'])) {
            $category_ids = array_map('absint', (array)$data['category_ids']);
            wp_set_object_terms($post_id, $category_ids, 'travel_category');
        }
        
        // Add meta fields
        if (!empty($data['visit_date'])) {
            update_post_meta($post_id, '_visit_date', sanitize_text_field($data['visit_date']));
        }
        
        if (!empty($data['location'])) {
            update_post_meta($post_id, '_location', sanitize_text_field($data['location']));
        }
        
        if (!empty($data['coordinates'])) {
            update_post_meta($post_id, '_coordinates', [
                'lat' => floatval($data['coordinates']['lat']),
                'lng' => floatval($data['coordinates']['lng']),
            ]);
        }
        
        return $post_id;
    }
    
    /**
     * Get user travels
     * 
     * @param int $user_id User ID
     * @return array Array of travel data
     */
    public function get_travels($user_id) {
        $user_id = absint($user_id);
        if (!$user_id) {
            return [];
        }
        
        $travels = get_user_meta($user_id, self::META_KEY, true);
        
        if (!is_array($travels)) {
            return [];
        }
        
        return $travels;
    }
    
    /**
     * Get travel count
     * 
     * @param int $user_id User ID
     * @return int Number of travels
     */
    public function get_travel_count($user_id) {
        $travels = $this->get_travels($user_id);
        return count($travels);
    }
    
    /**
     * Get countries count (unique)
     * 
     * @param int $user_id User ID
     * @return int Number of unique countries
     */
    public function get_countries_count($user_id) {
        $travels = $this->get_travels($user_id);
        $countries = [];
        
        foreach ($travels as $travel) {
            $destination_id = $travel['destination_id'] ?? 0;
            if (!$destination_id) continue;
            
            $terms = get_the_terms($destination_id, 'country');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $countries[$term->term_id] = true;
                }
            }
        }
        
        return count($countries);
    }
    
    /**
     * Get travel statistics
     * 
     * @param int $user_id User ID
     * @return array Statistics
     */
    public function get_stats($user_id) {
        $travels = $this->get_travels($user_id);
        
        return [
            'total_destinations' => count($travels),
            'countries' => $this->get_countries_count($user_id),
            'photos' => $this->count_photos($travels),
            'first_travel' => $this->get_first_travel_date($travels),
            'last_travel' => $this->get_last_travel_date($travels),
        ];
    }
    
    /**
     * Count total photos
     */
    private function count_photos($travels) {
        $count = 0;
        foreach ($travels as $travel) {
            if (!empty($travel['photos'])) {
                $count += count($travel['photos']);
            }
        }
        return $count;
    }
    
    /**
     * Get first travel date
     */
    private function get_first_travel_date($travels) {
        if (empty($travels)) return null;
        
        $dates = wp_list_pluck($travels, 'visit_date');
        sort($dates);
        return $dates[0] ?? null;
    }
    
    /**
     * Get last travel date
     */
    private function get_last_travel_date($travels) {
        if (empty($travels)) return null;
        
        $dates = wp_list_pluck($travels, 'visit_date');
        rsort($dates);
        return $dates[0] ?? null;
    }
    
    /**
     * Remove travel from user history
     * 
     * @param int $user_id User ID
     * @param int $destination_id Destination post ID
     * @return bool Success
     */
    public function remove_travel($user_id, $destination_id) {
        $user_id = absint($user_id);
        $destination_id = absint($destination_id);
        
        if (!$user_id || !$destination_id) {
            return false;
        }
        
        $travels = $this->get_travels($user_id);
        $filtered = [];
        
        foreach ($travels as $travel) {
            if ($travel['destination_id'] !== $destination_id) {
                $filtered[] = $travel;
            }
        }
        
        update_user_meta($user_id, self::META_KEY, $filtered);
        
        // Optionally delete destination post
        // wp_delete_post($destination_id, true);
        
        do_action('tmp_travel_removed', $user_id, $destination_id);
        
        return true;
    }
    
    /**
     * AJAX: Add travel
     */
    public function ajax_add_travel() {
        check_ajax_referer('tmp_add_travel_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please login to add travel', 'travel-membership-pro')]);
        }
        
        $user_id = get_current_user_id();
        $data = $_POST['data'] ?? [];
        
        if (empty($data['title'])) {
            wp_send_json_error(['message' => __('Destination title is required', 'travel-membership-pro')]);
        }
        
        $result = $this->add_travel($user_id, $data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Travel added successfully!', 'travel-membership-pro'),
            'destination_id' => $result,
        ]);
    }
    
    /**
     * AJAX: Remove travel
     */
    public function ajax_remove_travel() {
        check_ajax_referer('tmp_remove_travel_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please login', 'travel-membership-pro')]);
        }
        
        $user_id = get_current_user_id();
        $destination_id = absint($_POST['destination_id'] ?? 0);
        
        if (!$destination_id) {
            wp_send_json_error(['message' => __('Invalid destination', 'travel-membership-pro')]);
        }
        
        $result = $this->remove_travel($user_id, $destination_id);
        
        if ($result) {
            wp_send_json_success(['message' => __('Travel removed successfully!', 'travel-membership-pro')]);
        } else {
            wp_send_json_error(['message' => __('Failed to remove travel', 'travel-membership-pro')]);
        }
    }
}
