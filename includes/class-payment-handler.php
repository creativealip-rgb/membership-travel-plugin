<?php
/**
 * Payment Handler
 * Handles payment processing and integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Payment_Handler {
    
    private $payment_methods = [
        'bank_transfer' => 'Bank Transfer',
        'bca' => 'BCA',
        'mandiri' => 'Mandiri',
        'bni' => 'BNI',
        'bri' => 'BRI',
        'gopay' => 'GoPay',
        'ovo' => 'OVO',
        'dana' => 'DANA',
        'shopeepay' => 'ShopeePay',
    ];
    
    public function __construct() {
        add_action('wp_ajax_tmpb_get_payment_methods', [$this, 'ajax_get_payment_methods']);
    }
    
    /**
     * Get available payment methods
     */
    public function get_payment_methods() {
        $saved = get_option('tmpb_payment_methods', ['bank_transfer', 'ewallet']);
        
        $methods = [];
        foreach ($saved as $key) {
            if (isset($this->payment_methods[$key])) {
                $methods[$key] = [
                    'name' => $this->payment_methods[$key],
                    'instructions' => $this->get_payment_instructions($key),
                ];
            }
        }
        
        return $methods;
    }
    
    /**
     * Get payment instructions
     */
    private function get_payment_instructions($method) {
        $instructions = [
            'bank_transfer' => __('Transfer to: Bank BCA 1234567890 a.n PT Travel', 'travel-membership-pro'),
            'bca' => __('Transfer to: BCA 1234567890 a.n PT Travel', 'travel-membership-pro'),
            'mandiri' => __('Transfer to: Mandiri 1234567890 a.n PT Travel', 'travel-membership-pro'),
            'bni' => __('Transfer to: BNI 1234567890 a.n PT Travel', 'travel-membership-pro'),
            'bri' => __('Transfer to: BRI 1234567890 a.n PT Travel', 'travel-membership-pro'),
            'gopay' => __('Send to: 0812-3456-7890 (GoPay)', 'travel-membership-pro'),
            'ovo' => __('Send to: 0812-3456-7890 (OVO)', 'travel-membership-pro'),
            'dana' => __('Send to: 0812-3456-7890 (DANA)', 'travel-membership-pro'),
            'shopeepay' => __('Send to: 0812-3456-7890 (ShopeePay)', 'travel-membership-pro'),
        ];
        
        return $instructions[$method] ?? '';
    }
    
    /**
     * Get bank account details
     */
    public function get_bank_accounts() {
        return [
            'bca' => [
                'bank' => 'BCA',
                'account' => '1234567890',
                'name' => 'PT Travel',
            ],
            'mandiri' => [
                'bank' => 'Mandiri',
                'account' => '1234567890',
                'name' => 'PT Travel',
            ],
        ];
    }
    
    /**
     * Verify payment
     */
    public function verify_payment($booking_id) {
        $booking = get_post($booking_id);
        if (!$booking) return false;
        
        $status = get_post_meta($booking_id, '_booking_status', true);
        
        if ($status === 'payment_uploaded') {
            // Admin needs to verify manually
            return 'pending_verification';
        }
        
        return $status === 'paid';
    }
    
    /**
     * Confirm payment (admin)
     */
    public function confirm_payment($booking_id, $admin_id) {
        if (!user_can($admin_id, 'manage_options')) {
            return new WP_Error('unauthorized', __('Unauthorized', 'travel-membership-pro'));
        }
        
        $booking_manager = new TMP_Booking_Manager();
        $booking_manager->update_status($booking_id, 'paid');
        
        update_post_meta($booking_id, '_payment_confirmed_by', $admin_id);
        update_post_meta($booking_id, '_payment_confirmed_at', current_time('mysql'));
        
        return true;
    }
    
    /**
     * AJAX: Get payment methods
     */
    public function ajax_get_payment_methods() {
        check_ajax_referer('tmpb_public_nonce', 'nonce');
        
        wp_send_json_success([
            'methods' => $this->get_payment_methods(),
            'banks' => $this->get_bank_accounts(),
        ]);
    }
}
