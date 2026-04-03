<?php
/**
 * Tour Booking Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Booking_Settings {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    /**
     * Add settings page
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=tour',
            __('Booking Settings', 'travel-membership-pro'),
            __('Booking Settings', 'travel-membership-pro'),
            'manage_options',
            'tmp-booking-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Bank Accounts
        register_setting('tmp_booking_settings', 'tmp_bca_account');
        register_setting('tmp_booking_settings', 'tmp_bca_name');
        register_setting('tmp_booking_settings', 'tmp_mandiri_account');
        register_setting('tmp_booking_settings', 'tmp_mandiri_name');
        register_setting('tmp_booking_settings', 'tmp_bni_account');
        register_setting('tmp_booking_settings', 'tmp_bni_name');
        
        // E-Wallet
        register_setting('tmp_booking_settings', 'tmp_ewallet_number');
        register_setting('tmp_booking_settings', 'tmp_ewallet_name');
        
        // Payment Instructions
        register_setting('tmp_booking_settings', 'tmp_payment_instructions');
        register_setting('tmp_booking_settings', 'tmp_payment_notes');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap" style="max-width:800px;">
            <h1 style="margin-bottom:30px;">⚙️ Booking & Payment Settings</h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('tmp_booking_settings'); ?>
                <?php do_settings_sections('tmp_booking_settings'); ?>
                
                <!-- Bank Accounts -->
                <div style="background:#fff;padding:20px;border:1px solid #ccd0d4;border-radius:8px;margin-bottom:20px;">
                    <h2 style="margin-top:0;margin-bottom:20px;padding-bottom:15px;border-bottom:2px solid #eee;">🏦 Bank Accounts</h2>
                    
                    <h3 style="margin-bottom:15px;">BCA</h3>
                    <table class="form-table">
                        <tr>
                            <th><label>Account Number</label></th>
                            <td><input type="text" name="tmp_bca_account" value="<?php echo esc_attr(get_option('tmp_bca_account', '1234567890')); ?>" class="regular-text" placeholder="e.g., 1234567890"></td>
                        </tr>
                        <tr>
                            <th><label>Account Name</label></th>
                            <td><input type="text" name="tmp_bca_name" value="<?php echo esc_attr(get_option('tmp_bca_name', 'PT Travel')); ?>" class="regular-text" placeholder="e.g., PT Travel"></td>
                        </tr>
                    </table>
                    
                    <h3 style="margin-bottom:15px;">Mandiri</h3>
                    <table class="form-table">
                        <tr>
                            <th><label>Account Number</label></th>
                            <td><input type="text" name="tmp_mandiri_account" value="<?php echo esc_attr(get_option('tmp_mandiri_account', '9876543210')); ?>" class="regular-text" placeholder="e.g., 9876543210"></td>
                        </tr>
                        <tr>
                            <th><label>Account Name</label></th>
                            <td><input type="text" name="tmp_mandiri_name" value="<?php echo esc_attr(get_option('tmp_mandiri_name', 'PT Travel')); ?>" class="regular-text" placeholder="e.g., PT Travel"></td>
                        </tr>
                    </table>
                    
                    <h3 style="margin-bottom:15px;">BNI</h3>
                    <table class="form-table">
                        <tr>
                            <th><label>Account Number</label></th>
                            <td><input type="text" name="tmp_bni_account" value="<?php echo esc_attr(get_option('tmp_bni_account', '1122334455')); ?>" class="regular-text" placeholder="e.g., 1122334455"></td>
                        </tr>
                        <tr>
                            <th><label>Account Name</label></th>
                            <td><input type="text" name="tmp_bni_name" value="<?php echo esc_attr(get_option('tmp_bni_name', 'PT Travel')); ?>" class="regular-text" placeholder="e.g., PT Travel"></td>
                        </tr>
                    </table>
                </div>
                
                <!-- E-Wallet -->
                <div style="background:#fff;padding:20px;border:1px solid #ccd0d4;border-radius:8px;margin-bottom:20px;">
                    <h2 style="margin-top:0;margin-bottom:20px;padding-bottom:15px;border-bottom:2px solid #eee;">📱 E-Wallet</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th><label>Phone Number</label></th>
                            <td><input type="text" name="tmp_ewallet_number" value="<?php echo esc_attr(get_option('tmp_ewallet_number', '0812-3456-7890')); ?>" class="regular-text" placeholder="e.g., 0812-3456-7890"></td>
                        </tr>
                        <tr>
                            <th><label>Account Name</label></th>
                            <td><input type="text" name="tmp_ewallet_name" value="<?php echo esc_attr(get_option('tmp_ewallet_name', 'PT Travel')); ?>" class="regular-text" placeholder="e.g., PT Travel"></td>
                        </tr>
                    </table>
                </div>
                
                <!-- Payment Instructions -->
                <div style="background:#fff;padding:20px;border:1px solid #ccd0d4;border-radius:8px;margin-bottom:20px;">
                    <h2 style="margin-top:0;margin-bottom:20px;padding-bottom:15px;border-bottom:2px solid #eee;">📋 Payment Instructions</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th><label>Next Steps (HTML allowed)</label></th>
                            <td>
                                <textarea name="tmp_payment_instructions" rows="6" class="large-text"><?php echo esc_textarea(get_option('tmp_payment_instructions', '1. Make payment to one of the accounts above\n2. Take a screenshot of the payment receipt\n3. Go to My Bookings page\n4. Click "Upload Payment" for this booking\n5. Upload the payment receipt screenshot\n6. Wait for admin confirmation (1-2 business days)')); ?></textarea>
                                <p class="description">Use \n for new lines</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label>Additional Notes</label></th>
                            <td>
                                <textarea name="tmp_payment_notes" rows="3" class="large-text"><?php echo esc_textarea(get_option('tmp_payment_notes', 'Please include your booking code in the payment description')); ?></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button('Save Settings', 'primary', 'submit', false, ['style' => 'padding:12px 30px;font-size:1.1em;']); ?>
            </form>
            
            <!-- Shortcode Info -->
            <div style="background:#f9f9f9;padding:20px;border:1px solid #ccd0d4;border-radius:8px;margin-top:30px;">
                <h3 style="margin-top:0;">📝 Available Shortcodes</h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Shortcode</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[tmp_login]</code></td>
                            <td>Login form</td>
                        </tr>
                        <tr>
                            <td><code>[tmp_register]</code></td>
                            <td>Registration form</td>
                        </tr>
                        <tr>
                            <td><code>[my_bookings]</code></td>
                            <td>User booking history</td>
                        </tr>
                        <tr>
                            <td><code>[tour_list]</code></td>
                            <td>List all tours</td>
                        </tr>
                        <tr>
                            <td><code>[booking_form tour_id="123"]</code></td>
                            <td>Booking form for specific tour</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}

