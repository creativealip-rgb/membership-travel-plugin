<?php
/**
 * My Bookings Template - FIXED v11
 * Shows customer info & booking details
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    echo '<div class="tmpb-wrapper"><div class="tmpb-notice">';
    echo '<p>Please login to view your bookings</p>';
    echo '<a href="' . wp_login_url(get_permalink()) . '" class="tmpb-btn tmpb-btn-primary" style="margin-top:10px;">Login</a>';
    echo '</div></div>';
    return;
}
?>

<div class="tmpb-wrapper">

$user_id = get_current_user_id();
$user = wp_get_current_user();

// Query bookings from database
$bookings = get_posts([
    'post_type' => 'tour_booking',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => '_user_id',
            'value' => $user_id,
        ],
    ],
    'orderby' => 'date',
    'order' => 'DESC',
]);

?>

<div class="tmpb-my-bookings">
    <div class="tmpb-header">
        <div>
            <h2>🎫 My Bookings</h2>
            <p>Logged in as: <strong><?php echo esc_html($user->display_name); ?></strong> (<?php echo esc_html($user->user_email); ?>)</p>
        </div>
        <a href="<?php echo get_post_type_archive_link('tour'); ?>" class="tmpb-btn tmpb-btn-primary">Browse Tours</a>
    </div>
    
    <?php if (empty($bookings)): ?>
        <div class="tmpb-no-bookings">
            <p>🗺️</p>
            <h3>No Bookings Yet</h3>
            <p>You haven't made any bookings yet. Start exploring our tours!</p>
            <a href="<?php echo get_post_type_archive_link('tour'); ?>" class="tmpb-btn tmpb-btn-primary tmpb-btn-lg">Browse Tours</a>
        </div>
    <?php else: ?>
        <div class="tmpb-bookings-list">
            <?php foreach ($bookings as $booking): 
                $booking_id = $booking->ID;
                $tour_id = get_post_meta($booking_id, '_tour_id', true);
                $tour = get_post($tour_id);
                $status = get_post_meta($booking_id, '_booking_status', true);
                $booking_code = get_post_meta($booking_id, '_booking_code', true);
                $total = get_post_meta($booking_id, '_total_amount', true);
                $pax = get_post_meta($booking_id, '_pax', true);
                $travel_date = get_post_meta($booking_id, '_travel_date', true);
                $booking_date = get_post_meta($booking_id, '_booking_date', true);
                
                // Customer info
                $customer_name = get_post_meta($booking_id, '_customer_name', true);
                $customer_email = get_post_meta($booking_id, '_customer_email', true);
                $customer_phone = get_post_meta($booking_id, '_customer_phone', true);
                
                // Status badges
                $status_labels = [
                    'pending_payment' => ['label' => '⏳ Pending Payment', 'class' => 'pending', 'color' => '#fff3cd'],
                    'payment_uploaded' => ['label' => '📤 Payment Uploaded', 'class' => 'uploaded', 'color' => '#cce5ff'],
                    'paid' => ['label' => '✅ Paid', 'class' => 'paid', 'color' => '#d4edda'],
                    'confirmed' => ['label' => '✓ Confirmed', 'class' => 'confirmed', 'color' => '#d4edda'],
                    'cancelled' => ['label' => '❌ Cancelled', 'class' => 'cancelled', 'color' => '#f8d7da'],
                    'completed' => ['label' => '✔ Completed', 'class' => 'completed', 'color' => '#d4edda'],
                ];
                
                $status_info = $status_labels[$status] ?? ['label' => $status, 'class' => '', 'color' => '#f0f0f1'];
                $tour_thumbnail = get_the_post_thumbnail_url($tour_id, 'thumbnail');
            ?>
                <div class="tmpb-booking-item">
                    <div class="tmpb-booking-header">
                        <div class="tmpb-booking-code">
                            <span>Booking Code:</span>
                            <code><?php echo esc_html($booking_code); ?></code>
                        </div>
                        <span class="tmpb-booking-status" style="background:<?php echo esc_attr($status_info['color']); ?>;">
                            <?php echo esc_html($status_info['label']); ?>
                        </span>
                    </div>
                    
                    <div class="tmpb-booking-body">
                        <div class="tmpb-booking-tour">
                            <?php if ($tour_thumbnail): ?>
                                <img src="<?php echo esc_url($tour_thumbnail); ?>" alt="">
                            <?php else: ?>
                                <div style="width:120px;height:120px;background:var(--tmp-color-primary-gradient);border-radius:var(--tmp-radius-lg);display:flex;align-items:center;justify-content:center;font-size:3em;color:#fff;">🗺️</div>
                            <?php endif; ?>
                            
                            <div>
                                <h4><?php echo $tour ? esc_html($tour->post_title) : 'Tour Deleted'; ?></h4>
                                <p>📅 Travel Date: <strong><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($travel_date))); ?></strong></p>
                                <p>👥 <strong><?php echo esc_html($pax); ?></strong> persons</p>
                                <p>📅 Booked: <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($booking_date))); ?></p>
                            </div>
                        </div>
                        
                        <div class="tmpb-customer-info">
                            <h5>👤 Customer Information:</h5>
                            <div>
                                <div><strong>Name:</strong> <?php echo esc_html($customer_name); ?></div>
                                <div><strong>Email:</strong> <?php echo esc_html($customer_email); ?></div>
                                <div><strong>Phone:</strong> <?php echo esc_html($customer_phone); ?></div>
                            </div>
                        </div>
                        
                        <div class="tmpb-booking-footer">
                            <div class="tmpb-booking-total">
                                <span>Total Paid:</span>
                                <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                            </div>
                            
                            <div class="tmpb-booking-actions">
                                <a href="<?php echo get_permalink($tour_id); ?>" class="tmpb-btn tmpb-btn-secondary">View Tour</a>
                                
                                <?php if ($status === 'pending_payment'): ?>
                                    <button class="tmpb-btn tmpb-btn-warning tmpb-upload-payment" data-booking-id="<?php echo esc_attr($booking_id); ?>">📤 Upload Payment</button>
                                <?php endif; ?>
                                
                                <?php if (in_array($status, ['pending_payment', 'payment_uploaded', 'paid', 'confirmed'])): ?>
                                    <button class="tmpb-btn tmpb-btn-danger tmpb-cancel-booking" data-booking-id="<?php echo esc_attr($booking_id); ?>">Cancel Booking</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Cancel booking
    $('.tmpb-cancel-booking').on('click', function() {
        if (!confirm('Are you sure you want to cancel this booking?')) return;
        
        var bookingId = $(this).data('booking-id');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'tmpb_cancel_booking',
                nonce: '<?php echo wp_create_nonce('tmpb_booking_nonce'); ?>',
                booking_id: bookingId
            },
            success: function(response) {
                if (response.success) {
                    alert('Booking cancelled successfully');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('Failed to cancel booking. Please try again.');
            }
        });
    });
    
    // Upload payment
    $('.tmpb-upload-payment').on('click', function() {
        var bookingId = $(this).data('booking-id');
        var modalHtml = `
            <div id="tmpb-payment-modal" class="tmpb-modal-overlay">
                <div class="tmpb-modal-content">
                    <h3>📤 Upload Payment Proof</h3>
                    <form id="tmpb-payment-form">
                        <input type="hidden" name="booking_id" value="${bookingId}">
                        
                        <div class="tmpb-form-group">
                            <label>Payment Method</label>
                            <select name="method" required>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="gopay">GoPay</option>
                                <option value="ovo">OVO</option>
                                <option value="dana">DANA</option>
                                <option value="shopeepay">ShopeePay</option>
                            </select>
                        </div>
                        
                        <div class="tmpb-form-group">
                            <label>Payment Proof (Screenshot)</label>
                            <input type="file" name="payment_proof" accept="image/*" required>
                            <small style="color:var(--tmp-color-text-light);display:block;margin-top:var(--tmp-spacing-xs);">Upload screenshot of transfer receipt (JPG, PNG, max 5MB)</small>
                        </div>
                        
                        <div style="background:var(--tmp-color-warning-bg);padding:var(--tmp-spacing-lg);border-radius:var(--tmp-radius-md);border-left:4px solid var(--tmp-color-warning);margin-bottom:var(--tmp-spacing-lg);">
                            <strong>📋 Payment Instructions:</strong>
                            <ul style="margin:var(--tmp-spacing-sm) 0;padding-left:var(--tmp-spacing-xl);">
                                <li>BCA: 1234567890 a.n PT Travel</li>
                                <li>Mandiri: 9876543210 a.n PT Travel</li>
                                <li>GoPay/OVO: 0812-3456-7890</li>
                            </ul>
                        </div>
                        
                        <div style="display:flex;gap:var(--tmp-spacing-sm);">
                            <button type="submit" class="tmpb-btn tmpb-btn-primary" style="flex:1;">Upload Payment</button>
                            <button type="button" class="tmpb-btn tmpb-btn-secondary" onclick="document.getElementById('tmpb-payment-modal').remove()" style="flex:1;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        
        $('#tmpb-payment-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            formData.append('action', 'tmpb_upload_payment');
            formData.append('nonce', '<?php echo wp_create_nonce('tmpb_booking_nonce'); ?>');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('✅ Payment proof uploaded successfully! We will verify it within 1-2 business days.');
                        $('#tmpb-payment-modal').remove();
                        location.reload();
                    } else {
                        alert('❌ Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('❌ Upload failed. Please try again.');
                }
            });
        });
    });
});
</script>
</div>
