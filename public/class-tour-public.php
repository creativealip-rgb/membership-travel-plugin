<?php
/**
 * Tour Public Frontend
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Tour_Public {
    
    public function __construct() {
        add_shortcode('tour_list', [$this, 'tour_list']);
        add_shortcode('tour_single', [$this, 'tour_single']);
        add_shortcode('my_bookings', [$this, 'my_bookings']);
        add_shortcode('booking_form', [$this, 'booking_form']);
        
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_tour_styles']);
        
        // Use custom template for single tour pages
        add_filter('single_template', [$this, 'load_tour_template']);
        add_filter('archive_template', [$this, 'load_tour_archive_template']);
    }
    
    /**
     * Load custom single tour template
     */
    public function load_tour_template($template) {
        if (get_post_type() === 'tour') {
            $custom_template = TRAVEL_MEMBERSHIP_PRO_PATH . 'templates/tour-single-fixed.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
    
    /**
     * Load custom tour archive template
     */
    public function load_tour_archive_template($template) {
        if (is_post_type_archive('tour')) {
            $custom_template = TRAVEL_MEMBERSHIP_PRO_PATH . 'templates/archive-tour.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
    
    public function enqueue_assets() {
        wp_enqueue_style(
            'tmpb-public-css',
            TRAVEL_MEMBERSHIP_PRO_URL . 'public/css/tour-booking.css',
            [],
            TRAVEL_MEMBERSHIP_PRO_VERSION
        );
        
        wp_enqueue_script(
            'tmpb-public-js',
            TRAVEL_MEMBERSHIP_PRO_URL . 'public/js/tour-booking.js',
            ['jquery'],
            TRAVEL_MEMBERSHIP_PRO_VERSION,
            true
        );
        
        wp_localize_script('tmpb-public-js', 'tmpbAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tmpb_booking_nonce'),
            'publicNonce' => wp_create_nonce('tmpb_public_nonce'),
            'i18n' => [
                'bookingSuccess' => __('Booking successful!', 'travel-membership-pro'),
                'bookingError' => __('Booking failed', 'travel-membership-pro'),
                'confirmCancel' => __('Are you sure you want to cancel this booking?', 'travel-membership-pro'),
            ],
        ]);
    }
    
    /**
     * Enqueue styles for single tour pages
     */
    public function enqueue_tour_styles() {
        if (is_singular('tour') || is_post_type_archive('tour')) {
            wp_enqueue_style(
                'tmpb-public-css',
                TRAVEL_MEMBERSHIP_PRO_URL . 'public/css/tour-booking.css',
                [],
                TRAVEL_MEMBERSHIP_PRO_VERSION
            );
        }
    }
    
    /**
     * Tour List Shortcode
     * [tour_list]
     */
    public function tour_list($atts) {
        $atts = shortcode_atts([
            'limit' => '-1',
            'category' => '',
        ], $atts);
        
        $args = [
            'post_type' => 'tour',
            'posts_per_page' => absint($atts['limit']),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        // Filter by category if specified
        if (!empty($atts['category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'travel_category',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($atts['category']),
                ],
            ];
        }
        
        $tours = new WP_Query($args);
        
        ob_start();
        include TRAVEL_MEMBERSHIP_PRO_PATH . 'templates/tour-list.php';
        return ob_get_clean();
    }
    
    /**
     * Tour Single Shortcode
     * [tour_single id="123"]
     */
    public function tour_single($atts) {
        $atts = shortcode_atts(['id' => 0], $atts);
        $tour_id = absint($atts['id']);
        
        if (!$tour_id) {
            return '';
        }
        
        ob_start();
        include TRAVEL_MEMBERSHIP_PRO_PATH . 'templates/tour-single.php';
        return ob_get_clean();
    }
    
    /**
     * My Bookings Shortcode
     * [my_bookings]
     */
    public function my_bookings($atts) {
        if (!is_user_logged_in()) {
            return '<div class="tmpb-notice" style="padding:20px;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;text-align:center;">Please login to view your bookings. <a href="' . wp_login_url(get_permalink()) . '">Login here</a></div>';
        }
        
        ob_start();
        include TRAVEL_MEMBERSHIP_PRO_PATH . 'templates/my-bookings-fixed.php';
        return ob_get_clean();
    }
    
    /**
     * Booking Form Shortcode
     * [booking_form tour_id="123"]
     */
    public function booking_form($atts) {
        $atts = shortcode_atts(['tour_id' => 0], $atts);
        $tour_id = absint($atts['tour_id']);
        
        if (!$tour_id) {
            return '';
        }
        
        if (!is_user_logged_in()) {
            return '<div class="tmpb-notice">' . __('Please login to book this tour', 'travel-membership-pro') . '</div>';
        }
        
        ob_start();
        include TRAVEL_MEMBERSHIP_PRO_PATH . 'templates/booking-form.php';
        return ob_get_clean();
    }
}
