<?php
/**
 * Tour Admin - Simplified
 * Booking management for admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Tour_Admin {
    
    public function __construct() {
        add_action('wp_ajax_tmpb_admin_update_status', [$this, 'ajax_update_status']);
        add_action('add_meta_boxes', [$this, 'add_booking_meta_boxes']);
        add_action('save_post_tour_booking', [$this, 'save_booking_meta'], 10, 2);
    }
    
    public function booking_columns($columns) {
        return [
            'cb' => '<input type="checkbox" />',
            'title' => __('Booking', 'travel-membership-pro'),
            'tour' => __('Tour', 'travel-membership-pro'),
            'customer' => __('Customer', 'travel-membership-pro'),
            'pax' => __('Pax', 'travel-membership-pro'),
            'total' => __('Total', 'travel-membership-pro'),
            'status' => __('Status', 'travel-membership-pro'),
            'date' => __('Date', 'travel-membership-pro'),
        ];
    }
    
    public function booking_column_content($column, $post_id) {
        switch ($column) {
            case 'tour':
                $tour_id = get_post_meta($post_id, '_tour_id', true);
                if ($tour_id) {
                    echo '<a href="' . get_edit_post_link($tour_id) . '">';
                    echo get_the_title($tour_id);
                    echo '</a>';
                }
                break;
                
            case 'customer':
                $user_id = get_post_meta($post_id, '_user_id', true);
                if ($user_id) {
                    $user = get_user_by('id', $user_id);
                    if ($user) {
                        echo '<a href="' . get_edit_user_link($user_id) . '">';
                        echo esc_html($user->display_name);
                        echo '</a><br>';
                        echo '<small>' . esc_html($user->user_email) . '</small>';
                    }
                }
                break;
                
            case 'pax':
                echo absint(get_post_meta($post_id, '_pax', true));
                break;
                
            case 'total':
                $total = absint(get_post_meta($post_id, '_total_amount', true));
                echo 'Rp ' . number_format($total, 0, ',', '.');
                break;
                
            case 'status':
                $status = get_post_meta($post_id, '_booking_status', true);
                $labels = [
                    'pending_payment' => '⏳ Pending',
                    'payment_uploaded' => '📤 Uploaded',
                    'paid' => '✅ Paid',
                    'confirmed' => '✓ Confirmed',
                    'cancelled' => '❌ Cancelled',
                    'completed' => '✔ Completed',
                ];
                echo '<span style="background:#f0f0f1;padding:4px 8px;border-radius:4px;font-size:12px;">' . 
                     ($labels[$status] ?? $status) . '</span>';
                break;
                
            case 'date':
                $date = get_post_meta($post_id, '_booking_date', true);
                echo $date ? date_i18n(get_option('date_format'), strtotime($date)) : '-';
                break;
        }
    }
    
    public function add_booking_meta_boxes() {
        add_meta_box(
            'tmpb_booking_details',
            __('Booking Details', 'travel-membership-pro'),
            [$this, 'render_booking_details'],
            'tour_booking',
            'normal',
            'high'
        );
    }
    
    public function render_booking_details($post) {
        wp_nonce_field('tmpb_booking_meta', 'tmpb_booking_meta_nonce');
        
        $tour_id = get_post_meta($post->ID, '_tour_id', true);
        $user_id = get_post_meta($post->ID, '_user_id', true);
        $pax = get_post_meta($post->ID, '_pax', true);
        $total = get_post_meta($post->ID, '_total_amount', true);
        $status = get_post_meta($post->ID, '_booking_status', true);
        $travel_date = get_post_meta($post->ID, '_travel_date', true);
        $customer_name = get_post_meta($post->ID, '_customer_name', true);
        $customer_email = get_post_meta($post->ID, '_customer_email', true);
        $customer_phone = get_post_meta($post->ID, '_customer_phone', true);
        $notes = get_post_meta($post->ID, '_notes', true);
        
        ?>
        <table class="form-table" style="width: 100%;">
            <tr>
                <th style="width: 200px;">Tour</th>
                <td><?php echo $tour_id ? get_the_title($tour_id) : 'N/A'; ?></td>
            </tr>
            <tr>
                <th>Customer Name</th>
                <td><?php echo esc_html($customer_name); ?></td>
            </tr>
            <tr>
                <th>Customer Email</th>
                <td><?php echo esc_html($customer_email); ?></td>
            </tr>
            <tr>
                <th>Customer Phone</th>
                <td><?php echo esc_html($customer_phone); ?></td>
            </tr>
            <tr>
                <th>Pax</th>
                <td><?php echo esc_html($pax); ?> persons</td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td>Rp <?php echo number_format(absint($total), 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <th>Travel Date</th>
                <td><?php echo esc_html($travel_date); ?></td>
            </tr>
            <tr>
                <th>Status *</th>
                <td>
                    <select name="_booking_status" style="width:100%;max-width:300px; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;">
                        <option value="pending_payment" <?php selected($status, 'pending_payment'); ?>>⏳ Pending Payment</option>
                        <option value="payment_uploaded" <?php selected($status, 'payment_uploaded'); ?>>📤 Payment Uploaded</option>
                        <option value="paid" <?php selected($status, 'paid'); ?>>✅ Paid</option>
                        <option value="confirmed" <?php selected($status, 'confirmed'); ?>>✓ Confirmed</option>
                        <option value="cancelled" <?php selected($status, 'cancelled'); ?>>❌ Cancelled</option>
                        <option value="completed" <?php selected($status, 'completed'); ?>>✔ Completed</option>
                    </select>
                    <p class="description">Select the booking status</p>
                </td>
            </tr>
            <tr>
                <th>Payment Proof</th>
                <td>
                    <?php
                    $payment_proof_id = get_post_meta($post->ID, '_payment_proof', true);
                    if ($payment_proof_id) {
                        $payment_url = wp_get_attachment_url($payment_proof_id);
                        echo '<a href="' . esc_url($payment_url) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: #dbeafe; color: #1e40af; text-decoration: none; border-radius: 6px; font-weight: 600;">📎 View Payment Proof</a>';
                    } else {
                        echo '<span style="color: #64748b;">No payment proof uploaded</span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Notes</th>
                <td>
                    <textarea name="_notes" rows="4" style="width:100%;max-width:500px; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px;"><?php echo esc_textarea($notes); ?></textarea>
                    <p class="description">Internal notes about this booking</p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function save_booking_meta($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['tmpb_booking_meta_nonce']) || 
            !wp_verify_nonce($_POST['tmpb_booking_meta_nonce'], 'tmpb_booking_meta')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check post type
        if ($post->post_type !== 'tour_booking') {
            return;
        }
        
        // Save booking status
        if (isset($_POST['_booking_status'])) {
            $valid_statuses = ['pending_payment', 'payment_uploaded', 'paid', 'confirmed', 'cancelled', 'completed'];
            $status = sanitize_text_field($_POST['_booking_status']);
            
            if (in_array($status, $valid_statuses)) {
                update_post_meta($post_id, '_booking_status', $status);
            }
        }
        
        // Save notes
        if (isset($_POST['_notes'])) {
            update_post_meta($post_id, '_notes', sanitize_textarea_field($_POST['_notes']));
        }
    }
    
    public function ajax_update_status() {
        check_ajax_referer('tmpb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $booking_id = absint($_POST['booking_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        
        if (!$booking_id || !$status) {
            wp_send_json_error(['message' => 'Invalid data']);
        }
        
        $valid_statuses = ['pending_payment', 'payment_uploaded', 'paid', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(['message' => 'Invalid status']);
        }
        
        update_post_meta($booking_id, '_booking_status', $status);
        
        wp_send_json_success(['message' => 'Status updated']);
    }
}

