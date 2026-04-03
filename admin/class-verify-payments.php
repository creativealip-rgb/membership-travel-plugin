<?php
/**
 * Verify Payments - Admin Menu
 * Payment verification for tour bookings
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Verify_Payments {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }
    
    /**
     * Add admin menu (as submenu under Tours)
     */
    public function add_admin_menu() {
        // Verify Payments submenu under Tours
        add_submenu_page(
            'edit.php?post_type=tour',
            __('Verify Payments', 'travel-membership-pro'),
            __('Verify Payments', 'travel-membership-pro'),
            'manage_options',
            'tmp-verify-payments',
            [$this, 'render_verify_page']
        );
    }
    
    /**
     * Render verify payments page
     */
    public function render_verify_page() {
        ?>
        <div class="wrap">
            <h1 style="margin-bottom: 20px;">✅ Verify Member Payments</h1>
            
            <?php
            // Get all users with pending payments
            $users = get_users(['meta_key' => '_tmp_pending_tier']);
            
            if (empty($users)) {
                echo '<div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; border-radius: 8px; margin-bottom: 24px;">';
                echo '<h2 style="margin: 0 0 8px 0; color: #166534;">🎉 All Caught Up!</h2>';
                echo '<p style="margin: 0; color: #166534;">No pending payment verifications at the moment.</p>';
                echo '</div>';
            } else {
                echo '<div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
                echo '<table class="wp-list-table widefat fixed striped" style="width: 100%;">';
                echo '<thead>';
                echo '<tr>';
                echo '<th style="padding: 12px;">User</th>';
                echo '<th style="padding: 12px;">Email</th>';
                echo '<th style="padding: 12px;">Tier</th>';
                echo '<th style="padding: 12px;">Order ID</th>';
                echo '<th style="padding: 12px;">Submitted</th>';
                echo '<th style="padding: 12px;">Actions</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                foreach ($users as $user) {
                    $tier = get_user_meta($user->ID, '_tmp_pending_tier', true);
                    $order_id = get_user_meta($user->ID, '_tmp_pending_order_id', true);
                    $payments = get_user_meta($user->ID, '_tmp_manual_payments', false);
                    $last_payment = end($payments);
                    
                    echo '<tr>';
                    echo '<td style="padding: 12px;"><strong>' . esc_html($user->display_name) . '</strong></td>';
                    echo '<td style="padding: 12px;">' . esc_html($user->user_email) . '</td>';
                    echo '<td style="padding: 12px;">' . esc_html(ucfirst($tier)) . '</td>';
                    echo '<td style="padding: 12px;">' . esc_html($order_id) . '</td>';
                    echo '<td style="padding: 12px;">' . (isset($last_payment['submitted_at']) ? esc_html($last_payment['submitted_at']) : 'N/A') . '</td>';
                    echo '<td style="padding: 12px;">';
                    echo '<a href="' . admin_url('user-edit.php?user_id=' . $user->ID) . '" class="button button-primary">View User</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            }
            ?>
        </div>
        <?php
    }
}

new TMP_Verify_Payments();
