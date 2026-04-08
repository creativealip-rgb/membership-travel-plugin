<?php
/**
 * Booking Manager
 * Handles all booking operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Booking_Manager {
    
    public function __construct() {
        add_action('init', [$this, 'register_booking_post_type']);
        add_action('wp_ajax_tmpb_create_booking', [$this, 'ajax_create_booking']);
        add_action('wp_ajax_tmpb_cancel_booking', [$this, 'ajax_cancel_booking']);
        add_action('wp_ajax_tmpb_upload_payment', [$this, 'ajax_upload_payment']);
    }
    
    public function register_booking_post_type() {
        register_post_type('tour_booking', [
            'labels' => [
                'name' => __('Bookings', 'travel-membership-pro'),
                'singular_name' => __('Booking', 'travel-membership-pro'),
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=tour',
            'capability_type' => 'post',
            'supports' => ['title'],
        ]);
        
        register_taxonomy('booking_status', 'tour_booking', [
            'public' => true,
            'show_ui' => true,
            'label' => __('Booking Status', 'travel-membership-pro'),
            'rewrite' => false,
        ]);
    }
    
    /**
     * Create booking
     */
    public function create_booking($user_id, $tour_id, $data) {
        $user_id = absint($user_id);
        $tour_id = absint($tour_id);
        
        // Validate tour
        $tour = get_post($tour_id);
        if (!$tour || $tour->post_type !== 'tour') {
            return new WP_Error('invalid_tour', __('Invalid tour', 'travel-membership-pro'));
        }
        
        // Check quota
        if (!$this->check_availability($tour_id, $data['pax'] ?? 1)) {
            return new WP_Error('no_quota', __('Tour is fully booked', 'travel-membership-pro'));
        }
        
        // Calculate total
        $price = absint(get_post_meta($tour_id, 'price', true));
        $pax = absint($data['pax'] ?? 1);
        $total = $price * $pax;
        
        // Create booking
        $booking_id = wp_insert_post([
            'post_type' => 'tour_booking',
            'post_status' => 'pending',
            'post_title' => sprintf(
                __('Booking #%d - %s', 'travel-membership-pro'),
                0,
                get_the_title($tour_id)
            ),
        ]);
        
        if (is_wp_error($booking_id)) {
            return $booking_id;
        }
        
        // Update title with ID
        wp_update_post([
            'ID' => $booking_id,
            'post_title' => sprintf(
                __('Booking #%d - %s', 'travel-membership-pro'),
                $booking_id,
                get_the_title($tour_id)
            ),
        ]);
        
        // Save booking meta
        update_post_meta($booking_id, '_booking_id', $booking_id);
        update_post_meta($booking_id, '_user_id', $user_id);
        update_post_meta($booking_id, '_tour_id', $tour_id);
        update_post_meta($booking_id, '_booking_status', 'pending_payment');
        update_post_meta($booking_id, '_total_amount', $total);
        update_post_meta($booking_id, '_pax', $pax);
        update_post_meta($booking_id, '_booking_date', current_time('mysql'));
        
        // Save customer details
        update_post_meta($booking_id, '_customer_name', sanitize_text_field($data['name'] ?? ''));
        update_post_meta($booking_id, '_customer_email', sanitize_email($data['email'] ?? ''));
        update_post_meta($booking_id, '_customer_phone', sanitize_text_field($data['phone'] ?? ''));
        
        // Save travel date
        update_post_meta($booking_id, '_travel_date', sanitize_text_field($data['travel_date'] ?? ''));
        
        // Save additional notes
        update_post_meta($booking_id, '_notes', sanitize_textarea_field($data['notes'] ?? ''));
        
        // Generate booking code
        $booking_code = 'TMPB-' . strtoupper(uniqid());
        update_post_meta($booking_id, '_booking_code', $booking_code);
        
        // Send notification
        $this->send_booking_notification($booking_id, 'new_booking');
        
        //do_action('tmpb_booking_created', $booking_id, $user_id, $tour_id);
        
        return $booking_id;
    }
    
    /**
     * Check tour availability
     */
    public function check_availability($tour_id, $pax = 1) {
        $quota = absint(get_post_meta($tour_id, 'quota', true));
        
        $booked = count(get_posts([
            'post_type' => 'tour_booking',
            'posts_per_page' => -1,
            'meta_query' => [
                ['key' => '_tour_id', 'value' => $tour_id],
                ['key' => '_booking_status', 'value' => ['confirmed', 'completed'], 'compare' => 'IN'],
            ],
        ]));
        
        return ($booked + $pax) <= $quota;
    }
    
    /**
     * Get user bookings
     */
    public function get_user_bookings($user_id, $status = 'all') {
        $args = [
            'post_type' => 'tour_booking',
            'posts_per_page' => -1,
            'meta_query' => [
                ['key' => '_user_id', 'value' => $user_id],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        if ($status !== 'all') {
            $args['meta_query'][] = [
                'key' => '_booking_status',
                'value' => $status,
            ];
        }
        
        return get_posts($args);
    }
    
    /**
     * Get booking details
     */
    public function get_booking_details($booking_id) {
        $booking = get_post($booking_id);
        if (!$booking) return null;
        
        $tour_id = get_post_meta($booking_id, '_tour_id', true);
        $tour = get_post($tour_id);
        
        return [
            'booking_id' => $booking_id,
            'booking_code' => get_post_meta($booking_id, '_booking_code', true),
            'status' => get_post_meta($booking_id, '_booking_status', true),
            'tour' => [
                'id' => $tour_id,
                'title' => $tour ? $tour->post_title : '',
                'thumbnail' => get_the_post_thumbnail_url($tour_id, 'medium'),
            ],
            'customer' => [
                'name' => get_post_meta($booking_id, '_customer_name', true),
                'email' => get_post_meta($booking_id, '_customer_email', true),
                'phone' => get_post_meta($booking_id, '_customer_phone', true),
            ],
            'pax' => absint(get_post_meta($booking_id, '_pax', true)),
            'total' => absint(get_post_meta($booking_id, '_total_amount', true)),
            'travel_date' => get_post_meta($booking_id, '_travel_date', true),
            'booking_date' => get_post_meta($booking_id, '_booking_date', true),
            'notes' => get_post_meta($booking_id, '_notes', true),
            'payment' => [
                'method' => get_post_meta($booking_id, '_payment_method', true),
                'proof' => get_post_meta($booking_id, '_payment_proof', true),
                'paid_at' => get_post_meta($booking_id, '_payment_paid_at', true),
            ],
        ];
    }
    
    /**
     * Update booking status
     */
    public function update_status($booking_id, $status) {
        update_post_meta($booking_id, '_booking_status', sanitize_text_field($status));
        
        $valid_statuses = ['pending_payment', 'paid', 'confirmed', 'cancelled', 'completed', 'refunded'];
        if (in_array($status, $valid_statuses)) {
            wp_set_object_terms($booking_id, $status, 'booking_status');
        }
        
        $this->send_booking_notification($booking_id, 'status_changed', $status);
        
        do_action('tmpb_booking_status_changed', $booking_id, $status);
    }
    
    /**
     * Cancel booking
     */
    public function cancel_booking($booking_id, $user_id) {
        $booking_user_id = absint(get_post_meta($booking_id, '_user_id', true));
        
        if ($booking_user_id !== $user_id && !current_user_can('manage_options')) {
            return new WP_Error('unauthorized', __('Unauthorized', 'travel-membership-pro'));
        }
        
        $status = get_post_meta($booking_id, '_booking_status', true);
        
        if (in_array($status, ['cancelled', 'completed', 'refunded'])) {
            return new WP_Error('already_processed', __('Booking already processed', 'travel-membership-pro'));
        }
        
        $this->update_status($booking_id, 'cancelled');
        
        return true;
    }
    
    /**
     * Upload payment proof
     */
    public function upload_payment($booking_id, $user_id, $payment_data) {
        $booking_user_id = absint(get_post_meta($booking_id, '_user_id', true));
        
        if ($booking_user_id !== $user_id) {
            return new WP_Error('unauthorized', __('Unauthorized', 'travel-membership-pro'));
        }
        
        $status = get_post_meta($booking_id, '_booking_status', true);
        if (!in_array($status, ['pending_payment', 'payment_uploaded'])) {
            return new WP_Error('invalid_status', __('Booking payment already processed', 'travel-membership-pro'));
        }
        
        // Handle file upload (frontend-safe; avoid hard failure on media_handle_upload)
        if (!empty($_FILES['payment_proof']) && !empty($_FILES['payment_proof']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $upload = wp_handle_upload($_FILES['payment_proof'], ['test_form' => false]);
            if (!empty($upload['error'])) {
                return new WP_Error('upload_error', sanitize_text_field($upload['error']));
            }

            $attachment = [
                'post_mime_type' => $upload['type'] ?? 'image/jpeg',
                'post_title'     => sanitize_file_name(pathinfo($_FILES['payment_proof']['name'], PATHINFO_FILENAME)),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ];

            $attachment_id = wp_insert_attachment($attachment, $upload['file']);
            if (is_wp_error($attachment_id) || !$attachment_id) {
                return new WP_Error('attachment_error', __('Failed to save payment proof', 'travel-membership-pro'));
            }

            $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            if (!is_wp_error($attach_data) && !empty($attach_data)) {
                wp_update_attachment_metadata($attachment_id, $attach_data);
            }

            update_post_meta($booking_id, '_payment_proof', $attachment_id);
            update_post_meta($booking_id, '_payment_method', sanitize_text_field($payment_data['method'] ?? 'bank_transfer'));
            update_post_meta($booking_id, '_payment_uploaded_at', current_time('mysql'));

            $this->update_status($booking_id, 'payment_uploaded');

            return $attachment_id;
        }
        
        return new WP_Error('no_file', __('No payment proof uploaded', 'travel-membership-pro'));
    }
    
    /**
     * Send booking notification
     */
    private function send_booking_notification($booking_id, $type, $extra = null) {
        $booking = $this->get_booking_details($booking_id);
        if (!$booking) return;
        
        $user = get_user_by('id', get_post_meta($booking_id, '_user_id', true));
        if (!$user) return;
        
        $admin_email = get_option('admin_email');
        
        switch ($type) {
            case 'new_booking':
                // To user
                $subject = __('Booking Received - ', 'travel-membership-pro') . $booking['booking_code'];
                $message = sprintf(
                    __('Thank you for your booking! Your booking code is: %s. We will process it soon.', 'travel-membership-pro'),
                    $booking['booking_code']
                );
                wp_mail($user->user_email, $subject, $message);
                
                // To admin
                wp_mail($admin_email, 
                    __('New Booking: ', 'travel-membership-pro') . $booking['tour']['title'],
                    sprintf(__('New booking from %s. Code: %s', 'travel-membership-pro'), $user->user_email, $booking['booking_code'])
                );
                break;
                
            case 'status_changed':
                $subject = __('Booking Status Update - ', 'travel-membership-pro') . $booking['booking_code'];
                $message = sprintf(
                    __('Your booking status has been updated to: %s', 'travel-membership-pro'),
                    $extra
                );
                wp_mail($user->user_email, $subject, $message);
                break;
        }
    }
    
    /**
     * AJAX: Create booking
     */
    public function ajax_create_booking() {
        check_ajax_referer('tmpb_booking_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please login to book', 'travel-membership-pro')]);
        }
        
        $user_id = get_current_user_id();
        $tour_id = absint($_POST['tour_id'] ?? 0);
        $data = array_merge($_POST) ?? [];
        
        if (!$tour_id) {
            wp_send_json_error(['message' => __('Invalid tour', 'travel-membership-pro')]);
        }
        
        $booking_id = $this->create_booking($user_id, $tour_id, $data);
        
        if (is_wp_error($booking_id)) {
            wp_send_json_error(['message' => $booking_id->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Booking created successfully!', 'travel-membership-pro'),
            'booking_id' => $booking_id,
            'booking_code' => get_post_meta($booking_id, '_booking_code', true),
        ]);
    }
    
    /**
     * AJAX: Cancel booking
     */
    public function ajax_cancel_booking() {
        check_ajax_referer('tmpb_booking_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please login', 'travel-membership-pro')]);
        }
        
        $user_id = get_current_user_id();
        $booking_id = absint($_POST['booking_id'] ?? 0);
        
        $result = $this->cancel_booking($booking_id, $user_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('Booking cancelled', 'travel-membership-pro')]);
    }
    
    /**
     * AJAX: Upload payment
     */
    public function ajax_upload_payment() {
        check_ajax_referer('tmpb_booking_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please login', 'travel-membership-pro')]);
        }
        
        $user_id = get_current_user_id();
        $booking_id = absint($_POST['booking_id'] ?? 0);
        $payment_data = $_POST['payment_data'] ?? [];
        
        $result = $this->upload_payment($booking_id, $user_id, $payment_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('Payment proof uploaded!', 'travel-membership-pro')]);
    }
}
