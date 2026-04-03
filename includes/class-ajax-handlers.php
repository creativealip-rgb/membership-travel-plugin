<?php
/**
 * AJAX Handlers
 * Handle all AJAX requests
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Ajax_Handlers {
    
    public function __construct() {
        // Public AJAX (no login required)
        add_action('wp_ajax_tmp_get_destinations', [$this, 'get_destinations']);
        add_action('wp_ajax_nopriv_tmp_get_destinations', [$this, 'get_destinations']);
        
        // Protected AJAX (login required)
        add_action('wp_ajax_tmp_get_user_travels', [$this, 'get_user_travels']);
        add_action('wp_ajax_tmp_get_travel_stats', [$this, 'get_travel_stats']);
        add_action('wp_ajax_tmp_upload_photo', [$this, 'upload_photo']);
        add_action('wp_ajax_tmp_get_countries', [$this, 'get_countries']);
        add_action('wp_ajax_tmp_get_categories', [$this, 'get_categories']);
    }
    
    /**
     * Get destinations (for map display)
     */
    public function get_destinations() {
        check_ajax_referer('tmp_public_nonce', 'nonce');
        
        $args = [
            'post_type' => 'destination',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ];
        
        // Filter by country
        if (!empty($_GET['country'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'country',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['country']),
            ];
        }
        
        // Filter by category
        if (!empty($_GET['category'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'travel_category',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['category']),
            ];
        }
        
        $query = new WP_Query($args);
        $destinations = [];
        
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            $coordinates = get_post_meta($post_id, '_coordinates', true);
            $country_terms = get_the_terms($post_id, 'country');
            $category_terms = get_the_terms($post_id, 'travel_category');
            
            $destinations[] = [
                'id' => $post_id,
                'title' => get_the_title(),
                'excerpt' => get_the_excerpt(),
                'permalink' => get_permalink(),
                'thumbnail' => get_the_post_thumbnail_url($post_id, 'medium'),
                'coordinates' => $coordinates ?: null,
                'countries' => $country_terms ? wp_list_pluck($country_terms, 'name') : [],
                'categories' => $category_terms ? wp_list_pluck($category_terms, 'name') : [],
                'visit_date' => get_post_meta($post_id, '_visit_date', true),
            ];
        }
        
        wp_reset_postdata();
        
        wp_send_json_success([
            'destinations' => $destinations,
            'total' => $query->found_posts,
        ]);
    }
    
    /**
     * Get user travels
     */
    public function get_user_travels() {
        check_ajax_referer('tmp_user_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please login', 'travel-membership-pro')]);
        }
        
        $user_id = get_current_user_id();
        $tracker = new TMP_User_Travel_Tracker();
        $travels = $tracker->get_travels($user_id);
        
        // Enrich with destination data
        $enriched = [];
        foreach ($travels as $travel) {
            $post_id = $travel['destination_id'] ?? 0;
            if (!$post_id) continue;
            
            $enriched[] = [
                'travel' => $travel,
                'destination' => [
                    'id' => $post_id,
                    'title' => get_the_title($post_id),
                    'permalink' => get_permalink($post_id),
                    'thumbnail' => get_the_post_thumbnail_url($post_id, 'thumbnail'),
                    'excerpt' => get_the_excerpt($post_id),
                ],
            ];
        }
        
        wp_send_json_success([
            'travels' => $enriched,
            'total' => count($enriched),
        ]);
    }
    
    /**
     * Get user travel stats
     */
    public function get_travel_stats() {
        check_ajax_referer('tmp_user_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please login', 'travel-membership-pro')]);
        }
        
        $user_id = get_current_user_id();
        $tracker = new TMP_User_Travel_Tracker();
        $stats = $tracker->get_stats($user_id);
        
        // Add membership info
        $membership = new TMP_Membership_Checker();
        $stats['membership'] = [
            'status' => $membership->get_membership_status($user_id),
            'limit' => $membership->get_destination_limit($user_id),
            'can_add' => !is_wp_error($membership->can_add_destination($user_id)),
        ];
        
        wp_send_json_success($stats);
    }
    
    /**
     * Upload photo
     */
    public function upload_photo() {
        check_ajax_referer('tmp_upload_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please login', 'travel-membership-pro')]);
        }
        
        if (empty($_FILES['photo'])) {
            wp_send_json_error(['message' => __('No photo uploaded', 'travel-membership-pro')]);
        }
        
        $file = $_FILES['photo'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(['message' => __('Invalid file type', 'travel-membership-pro')]);
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(['message' => __('File too large (max 5MB)', 'travel-membership-pro')]);
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $attachment_id = media_handle_upload('photo', 0);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => $attachment_id->get_error_message()]);
        }
        
        wp_send_json_success([
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'thumbnail' => wp_get_attachment_thumb_url($attachment_id),
        ]);
    }
    
    /**
     * Get countries list
     */
    public function get_countries() {
        check_ajax_referer('tmp_public_nonce', 'nonce');
        
        $countries = get_terms([
            'taxonomy' => 'country',
            'hide_empty' => false,
        ]);
        
        if (is_wp_error($countries)) {
            wp_send_json_error(['message' => $countries->get_error_message()]);
        }
        
        $result = [];
        foreach ($countries as $country) {
            $result[] = [
                'id' => $country->term_id,
                'name' => $country->name,
                'slug' => $country->slug,
                'count' => $country->count,
            ];
        }
        
        wp_send_json_success(['countries' => $result]);
    }
    
    /**
     * Get categories list
     */
    public function get_categories() {
        check_ajax_referer('tmp_public_nonce', 'nonce');
        
        $categories = get_terms([
            'taxonomy' => 'travel_category',
            'hide_empty' => false,
        ]);
        
        if (is_wp_error($categories)) {
            wp_send_json_error(['message' => $categories->get_error_message()]);
        }
        
        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->count,
            ];
        }
        
        wp_send_json_success(['categories' => $result]);
    }
}
