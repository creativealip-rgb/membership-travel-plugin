<?php
/**
 * Booking Form Template - FIXED v11
 * Includes payment instructions after booking
 */

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$tour_id = absint($atts['tour_id']);
$tour = get_post($tour_id);
$price = get_post_meta($tour_id, 'price', true);
?>

<div class="tmpb-booking-form" data-tour-id="<?php echo esc_attr($tour_id); ?>" style="max-width:500px;margin:0 auto;padding:20px;background:#fff;border:1px solid #ddd;border-radius:8px;">
    <h3 style="margin-bottom:20px;">📝 Book This Tour</h3>
    
    <div class="tmpb-tour-summary" style="background:#f9f9f9;padding:15px;border-radius:8px;margin-bottom:20px;">
        <h4 style="margin:0 0 10px;"><?php echo esc_html($tour->post_title); ?></h4>
        <p style="margin:5px 0;color:#666;">Price: <strong>Rp <?php echo number_format(absint($price), 0, ',', '.'); ?></strong> per person</p>
    </div>
    
    <form id="tmpb-booking-form">
        <input type="hidden" name="tour_id" value="<?php echo esc_attr($tour_id); ?>">
        
        <div class="tmpb-form-group" style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;font-weight:600;">Number of Pax *</label>
            <input type="number" name="pax" min="1" value="1" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;">
        </div>
        
        <div class="tmpb-form-group" style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;font-weight:600;">Travel Date *</label>
            <input type="date" name="travel_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;">
        </div>
        
        <div class="tmpb-form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
            <div class="tmpb-form-group" style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Full Name *</label>
                <input type="text" name="name" value="<?php echo esc_attr($user->display_name); ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;">
            </div>
            
            <div class="tmpb-form-group" style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Email *</label>
                <input type="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;">
            </div>
        </div>
        
        <div class="tmpb-form-group" style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;font-weight:600;">Phone Number *</label>
            <input type="tel" name="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone_number', true)); ?>" placeholder="e.g., +62 812-3456-7890" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;">
        </div>
        
        <div class="tmpb-form-group" style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;font-weight:600;">Notes / Special Requests</label>
            <textarea name="notes" rows="3" placeholder="Any special requests or dietary requirements?" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;"></textarea>
        </div>
        
        <div class="tmpb-form-group" style="margin-bottom:15px;">
            <label style="display:block;margin-bottom:5px;font-weight:600;">Payment Method *</label>
            <select name="payment_method" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;">
                <option value="">Select Payment Method</option>
                <option value="bank_transfer">Bank Transfer (BCA, Mandiri, BNI)</option>
                <option value="gopay">GoPay</option>
                <option value="ovo">OVO</option>
                <option value="dana">DANA</option>
                <option value="shopeepay">ShopeePay</option>
            </select>
        </div>
        
        <div class="tmpb-booking-total" style="display:flex;justify-content:space-between;align-items:center;padding:15px;background:#f0f0f1;border-radius:8px;margin:20px 0;">
            <strong>Total Payment:</strong>
            <span class="tmpb-total-amount" data-price="<?php echo absint($price); ?>" style="font-size:1.5em;font-weight:bold;color:#0073aa;">Rp 0</span>
        </div>
        
        <button type="submit" class="tmpb-btn tmpb-btn-primary tmpb-btn-block" style="background:#0073aa;color:#fff;padding:15px;font-size:1.1em;">📦 Book Now</button>
    </form>
    
    <!-- Payment Instructions (shown after booking) -->
    <div class="tmpb-payment-instructions" id="tmpb-payment-instructions" style="display:none;margin-top:30px;padding:20px;background:#e8f5e9;border:1px solid #4caf50;border-radius:8px;">
        <h3 style="color:#2e7d32;margin-bottom:15px;">✅ Booking Successful!</h3>
        
        <div class="tmpb-booking-details" style="background:#fff;padding:15px;border-radius:8px;margin-bottom:20px;">
            <p><strong>Booking Code:</strong> <code id="tmpb-booking-code" style="background:#f0f0f1;padding:4px 8px;border-radius:4px;font-size:1.2em;"></code></p>
            <p><strong>Total Amount:</strong> <span id="tmpb-booking-total" style="color:#0073aa;font-weight:bold;font-size:1.2em;"></span></p>
        </div>
        
        <h4 style="margin-bottom:10px;">💳 Payment Instructions:</h4>
        
        <div class="tmpb-payment-method" style="background:#fff;padding:15px;border-radius:8px;margin-bottom:15px;">
            <h5 style="margin-bottom:10px;"><strong>Bank Transfer:</strong></h5>
            <ul style="list-style:disc;padding-left:20px;margin:10px 0;">
                <li><strong>BCA:</strong> 1234567890 a.n PT Travel</li>
                <li><strong>Mandiri:</strong> 9876543210 a.n PT Travel</li>
                <li><strong>BNI:</strong> 1122334455 a.n PT Travel</li>
            </ul>
            <p style="color:#666;font-size:0.9em;">Please transfer the total amount to one of the accounts above.</p>
        </div>
        
        <div class="tmpb-payment-method" style="background:#fff;padding:15px;border-radius:8px;margin-bottom:15px;">
            <h5 style="margin-bottom:10px;"><strong>E-Wallet:</strong></h5>
            <ul style="list-style:disc;padding-left:20px;margin:10px 0;">
                <li><strong>GoPay/OVO/DANA:</strong> 0812-3456-7890</li>
            </ul>
            <p style="color:#666;font-size:0.9em;">Send the total amount to the number above.</p>
        </div>
        
        <div class="tmpb-next-steps" style="background:#fff3cd;padding:15px;border-radius:8px;border-left:4px solid #ffc107;">
            <h5 style="margin-bottom:10px;">📋 Next Steps:</h5>
            <ol style="padding-left:20px;margin:10px 0;">
                <li>Make payment to one of the accounts above</li>
                <li>Take a screenshot of the payment receipt</li>
                <li>Go to <strong>My Bookings</strong> page</li>
                <li>Click "Upload Payment" for this booking</li>
                <li>Upload the payment receipt screenshot</li>
                <li>Wait for admin confirmation (1-2 business days)</li>
            </ol>
        </div>
        
        <div class="tmpb-actions" style="margin-top:20px;">
            <a href="<?php echo esc_url(function_exists('contenly_localized_url') ? contenly_localized_url('/my-travels/') : home_url('/my-travels/')); ?>" class="tmpb-btn tmpb-btn-primary" style="background:#0073aa;color:#fff;padding:10px 20px;text-decoration:none;display:inline-block;">📋 View My Bookings</a>
            <a href="<?php echo get_permalink($tour_id); ?>" class="tmpb-btn tmpb-btn-secondary" style="background:#6c757d;color:#fff;padding:10px 20px;text-decoration:none;display:inline-block;margin-left:10px;">🔙 Back to Tour</a>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var $form = $('#tmpb-booking-form');
    var $paxInput = $form.find('input[name="pax"]');
    var $totalAmount = $form.find('.tmpb-total-amount');
    var price = parseInt($totalAmount.data('price')) || 0;
    
    // Calculate total
    $paxInput.on('change', function() {
        var pax = parseInt($(this).val()) || 1;
        var total = price * pax;
        $totalAmount.text('Rp ' + total.toLocaleString('id-ID'));
    }).trigger('change');
    
    // Submit form
    $form.on('submit', function(e) {
        e.preventDefault();
        
        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text('Processing...');
        
        var formData = new FormData(this);
        formData.append('action', 'tmpb_create_booking');
        formData.append('nonce', '<?php echo wp_create_nonce('tmpb_booking_nonce'); ?>');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Hide form, show payment instructions
                    $form.hide();
                    $('#tmpb-payment-instructions').show();
                    
                    // Set booking details
                    $('#tmpb-booking-code').text(response.data.booking_code);
                    $('#tmpb-booking-total').text($totalAmount.text());
                    
                    // Scroll to payment instructions
                    $('html, body').animate({
                        scrollTop: $('#tmpb-payment-instructions').offset().top - 100
                    }, 500);
                } else {
                    alert('Error: ' + response.data.message);
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('Booking failed. Please try again.');
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>
