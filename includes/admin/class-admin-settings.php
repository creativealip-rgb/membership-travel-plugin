<?php
namespace TravelShip\Admin;

if (!defined('ABSPATH')) exit;

class AdminSettings {

    private $option_key = 'travelship_settings';

    public function render() {
        // Handle save
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce($_POST['_travelship_nonce'] ?? '', 'travelship_save_settings')) {
            $settings = [
                'business_name'        => sanitize_text_field($_POST['business_name'] ?? ''),
                'business_phone'       => sanitize_text_field($_POST['business_phone'] ?? ''),
                'business_email'       => sanitize_email($_POST['business_email'] ?? ''),
                'business_address'     => sanitize_textarea_field($_POST['business_address'] ?? ''),
                'currency'             => sanitize_text_field($_POST['currency'] ?? 'IDR'),
                'auto_confirm'         => isset($_POST['auto_confirm']) ? 1 : 0,
                'payment_deadline'     => (int)($_POST['payment_deadline'] ?? 24),
                'enable_email_booking' => isset($_POST['enable_email_booking']) ? 1 : 0,
                'enable_email_status'  => isset($_POST['enable_email_status']) ? 1 : 0,
                'enable_email_reminder'=> isset($_POST['enable_email_reminder']) ? 1 : 0,
                'reminder_days_before' => (int)($_POST['reminder_days_before'] ?? 3),
                'bank_name'            => sanitize_text_field($_POST['bank_name'] ?? ''),
                'bank_account'         => sanitize_text_field($_POST['bank_account'] ?? ''),
                'bank_holder'          => sanitize_text_field($_POST['bank_holder'] ?? ''),
            ];

            update_option($this->option_key, $settings);
            echo '<div class="notice notice-success"><p>Pengaturan berhasil disimpan.</p></div>';
        }

        $settings = get_option($this->option_key, $this->defaults());

        include TRAVELSHIP_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    private function defaults() {
        return [
            'business_name'        => get_bloginfo('name'),
            'business_phone'       => '',
            'business_email'       => get_option('admin_email'),
            'business_address'     => '',
            'currency'             => 'IDR',
            'auto_confirm'         => 0,
            'payment_deadline'     => 24,
            'enable_email_booking' => 1,
            'enable_email_status'  => 1,
            'enable_email_reminder'=> 1,
            'reminder_days_before' => 3,
            'bank_name'            => '',
            'bank_account'         => '',
            'bank_holder'          => '',
        ];
    }

    /**
     * Get a single setting value.
     */
    public static function get($key, $default = '') {
        $settings = get_option('travelship_settings', []);
        return $settings[$key] ?? $default;
    }
}
