<?php
/**
 * My Bookings Template - FIXED v9
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    echo '<div class="tmpb-notice" style="padding:20px;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;text-align:center;">';
    echo '<p>Please login to view your bookings</p>';
    echo '<a href="' . wp_login_url(get_permalink()) . '" class="tmpb-btn tmpb-btn-primary">Login</a>';
    echo '</div>';
    return;
}

$user_id = get_current_user_id();

// Query bookings from database
$bookings = get_posts([
    'post_type' => 'tour_booking',
    'posts_per_page' => -1,
    'post_status' => 'any',
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

<div class="tmpb-my-bookings" style="max-width:1200px;margin:0 auto;padding:20px;">
    <h2 style="margin-bottom:30px;">🎫 My Tour Bookings</h2>
    
    <?php if (empty($bookings)): ?>
        <div class="tmpb-no-bookings" style="text-align:center;padding:40px;background:#f9f9f9;border-radius:8px;">
            <p style="font-size:3em;margin:0;">🗺️</p>
            <p style="margin:20px 0;">You have not made any bookings yet.</p>
            <a href="<?php echo get_post_type_archive_link('tour'); ?>" class="tmpb-btn tmpb-btn-primary">Browse Tours</a>
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
                
                $status_labels = [
                    'pending_payment' => ['label' => '⏳ Pending Payment', 'class' => 'pending'],
                    'payment_uploaded' => ['label' => '📤 Payment Uploaded', 'class' => 'uploaded'],
                    'paid' => ['label' => '✅ Paid', 'class' => 'paid'],
                    'confirmed' => ['label' => '✓ Confirmed', 'class' => 'confirmed'],
                    'cancelled' => ['label' => '❌ Cancelled', 'class' => 'cancelled'],
                    'completed' => ['label' => '✔ Completed', 'class' => 'completed'],
                ];
                
                $status_info = $status_labels[$status] ?? ['label' => $status, 'class' => ''];
                $tour_thumbnail = get_the_post_thumbnail_url($tour_id, 'thumbnail');
            ?>
                <div class="tmpb-booking-item" style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;margin:20px 0;">
                    <div class="tmpb-booking-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid #eee;">
                        <div class="tmpb-booking-code">
                            <strong>Booking Code:</strong>
                            <code style="background:#f0f0f1;padding:4px 8px;border-radius:4px;font-size:1.1em;margin-left:10px;"><?php echo esc_html($booking_code); ?></code>
                        </div>
                        <span class="tmpb-booking-status tmpb-status-<?php echo esc_attr($status_info['class']); ?>" style="padding:6px 12px;border-radius:20px;font-size:0.9em;background:<?php echo $status === 'confirmed' ? '#d4edda' : ($status === 'pending_payment' ? '#fff3cd' : '#cce5ff'); ?>;">
                            <?php echo esc_html($status_info['label']); ?>
                        </span>
                    </div>
                    
                    <div class="tmpb-booking-body">
                        <div class="tmpb-booking-tour" style="display:flex;gap:20px;margin-bottom:20px;">
                            <?php if ($tour_thumbnail): ?>
                                <img src="<?php echo esc_url($tour_thumbnail); ?>" alt="" style="width:100px;height:100px;object-fit:cover;border-radius:8px;">
                            <?php else: ?>
                                <div style="width:100px;height:100px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:2em;">🗺️</div>
                            <?php endif; ?>
                            
                            <div style="flex:1;">
                                <h4 style="margin:0 0 10px;"><?php echo $tour ? esc_html($tour->post_title) : 'Tour Deleted'; ?></h4>
                                <p style="margin:5px 0;">📅 Travel Date: <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($travel_date))); ?></p>
                                <p style="margin:5px 0;">👥 <?php echo esc_html($pax); ?> persons</p>
                                <p style="margin:5px 0;">📅 Booked: <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($booking_date))); ?></p>
                            </div>
                        </div>
                        
                        <div class="tmpb-booking-footer" style="display:flex;justify-content:space-between;align-items:center;padding-top:15px;border-top:1px solid #eee;">
                            <div class="tmpb-booking-total">
                                <strong>Total:</strong>
                                <span style="font-size:1.3em;color:#0073aa;margin-left:10px;">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                            </div>
                            
                            <div class="tmpb-booking-actions" style="display:flex;gap:10px;">
                                <?php if ($status === 'pending_payment'): ?>
                                    <button class="tmpb-btn tmpb-btn-warning tmpb-upload-payment" data-booking-id="<?php echo esc_attr($booking_id); ?>" style="background:#ffc107;color:#000;">Upload Payment</button>
                                <?php endif; ?>
                                
                                <?php if (in_array($status, ['pending_payment', 'payment_uploaded', 'paid', 'confirmed'])): ?>
                                    <button class="tmpb-btn tmpb-btn-danger tmpb-cancel-booking" data-booking-id="<?php echo esc_attr($booking_id); ?>" style="background:#dc3545;color:#fff;">Cancel</button>
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
                    alert('Booking cancelled');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            }
        });
    });
    
    // Upload payment
    $('.tmpb-upload-payment').on('click', function() {
        var bookingId = $(this).data('booking-id');
        alert('Payment upload feature - Contact admin for payment details. Booking ID: ' + bookingId);
    });
});
</script>
