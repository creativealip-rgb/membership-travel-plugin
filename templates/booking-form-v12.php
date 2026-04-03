<?php
/**
 * Booking Form Template - REV-12
 * With dynamic payment instructions from settings
 */

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$tour_id = absint($atts['tour_id']);
$tour = get_post($tour_id);
$price = get_post_meta($tour_id, 'price', true);

// Get payment settings
$bca_account = get_option('tmp_bca_account', '1234567890');
$bca_name = get_option('tmp_bca_name', 'PT Travel');
$mandiri_account = get_option('tmp_mandiri_account', '9876543210');
$mandiri_name = get_option('tmp_mandiri_name', 'PT Travel');
$bni_account = get_option('tmp_bni_account', '1122334455');
$bni_name = get_option('tmp_bni_name', 'PT Travel');
$ewallet_number = get_option('tmp_ewallet_number', '0812-3456-7890');
$ewallet_name = get_option('tmp_ewallet_name', 'PT Travel');
$payment_instructions = get_option('tmp_payment_instructions', '');
$payment_notes = get_option('tmp_payment_notes', '');
?>

<div class="tmpb-wrapper">
<div class="tmpb-booking-form" data-tour-id="<?php echo esc_attr($tour_id); ?>">
    <h3>📝 Book This Tour</h3>
    
    <div class="tmpb-tour-summary">
        <h4><?php echo esc_html($tour->post_title); ?></h4>
        <p>Price: <strong>Rp <?php echo number_format(absint($price), 0, ',', '.'); ?></strong> per person</p>
    </div>
    
    <form id="tmpb-booking-form">
        <input type="hidden" name="tour_id" value="<?php echo esc_attr($tour_id); ?>">
        
        <div class="tmpb-form-group">
            <label>Number of Pax *</label>
            <input type="number" name="pax" id="tmpb-pax" min="1" value="1" required>
        </div>
        
        <div class="tmpb-form-group">
            <label>Travel Date *</label>
            <input type="date" name="travel_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
        </div>
        
        <div class="tmpb-form-row">
            <div class="tmpb-form-group">
                <label>Full Name *</label>
                <input type="text" name="name" value="<?php echo esc_attr($user->display_name); ?>" required>
            </div>
            
            <div class="tmpb-form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" required>
            </div>
        </div>
        
        <div class="tmpb-form-group">
            <label>Phone Number *</label>
            <input type="tel" name="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone_number', true)); ?>" placeholder="+62 812-3456-7890" required>
        </div>
        
        <div class="tmpb-form-group">
            <label>Notes / Special Requests</label>
            <textarea name="notes" rows="3" placeholder="Any special requests or dietary requirements?" style="resize:vertical;"></textarea>
        </div>
        
        <div class="tmpb-form-group">
            <label>Payment Method *</label>
            <select name="payment_method" required>
                <option value="">Select Payment Method</option>
                <option value="bca">Bank Transfer - BCA</option>
                <option value="mandiri">Bank Transfer - Mandiri</option>
                <option value="bni">Bank Transfer - BNI</option>
                <option value="gopay">GoPay</option>
                <option value="ovo">OVO</option>
                <option value="dana">DANA</option>
                <option value="shopeepay">ShopeePay</option>
            </select>
        </div>
        
        <div class="tmpb-booking-total">
            <div>
                <strong>Total Payment:</strong>
                <small>Based on <span id="tmpb-pax-display">1</span> person(s)</small>
            </div>
            <span class="tmpb-total-amount" data-price="<?php echo absint($price); ?>">Rp 0</span>
        </div>
        
        <button type="submit" class="tmpb-btn tmpb-btn-primary tmpb-btn-block tmpb-btn-lg">📦 Book Now - Confirm Booking</button>
    </form>
    
    <!-- Payment Instructions (shown after booking) -->
    <div class="tmpb-payment-instructions" id="tmpb-payment-instructions" style="display:none;">
        <div class="tmpb-booking-details" style="background: var(--tmp-color-success-bg); padding: var(--tmp-spacing-xl); border-radius: var(--tmp-radius-lg); margin-bottom: var(--tmp-spacing-lg); text-align: center; border: 2px solid var(--tmp-color-success);">
            <h3 style="margin: 0 0 var(--tmp-spacing-sm); color: var(--tmp-color-success); font-size: var(--tmp-font-size-h2);">✅ Booking Successful!</h3>
            <p style="margin: 0; color: var(--tmp-color-text-medium);">Your tour has been booked successfully</p>
        </div>
        
        <div class="tmpb-booking-details" style="background:#fff;padding: var(--tmp-spacing-lg); border: 2px solid var(--tmp-color-success); border-radius: var(--tmp-radius-lg); margin-bottom: var(--tmp-spacing-lg);">
            <p style="margin: var(--tmp-spacing-sm) 0; font-size: var(--tmp-font-size-body);"><strong>Booking Code:</strong> <code id="tmpb-booking-code" style="background: var(--tmp-color-primary-light); padding: var(--tmp-spacing-xs) var(--tmp-spacing-sm); border-radius: var(--tmp-radius-sm); font-size: 1.2em; margin-left: var(--tmp-spacing-sm); font-weight: 700; color: var(--tmp-color-primary);"></code></p>
            <p style="margin: var(--tmp-spacing-sm) 0; font-size: var(--tmp-font-size-body);"><strong>Total Amount:</strong> <span id="tmpb-booking-total" style="color: var(--tmp-color-primary); font-weight: 700; font-size: 1.3em;"></span></p>
        </div>
        
        <div class="tmpb-payment-methods">
            <h4>💳 Payment Instructions:</h4>
            
            <div style="margin-bottom: var(--tmp-spacing-xl);">
                <h5><strong>🏦 Bank Transfer:</strong></h5>
                <div style="background: var(--tmp-color-background); padding: var(--tmp-spacing-lg); border-radius: var(--tmp-radius-md); border-left: 4px solid var(--tmp-color-primary);">
                    <p style="margin: var(--tmp-spacing-sm) 0;"><strong>BCA:</strong> <?php echo esc_html($bca_account); ?> a.n <?php echo esc_html($bca_name); ?></p>
                    <p style="margin: var(--tmp-spacing-sm) 0;"><strong>Mandiri:</strong> <?php echo esc_html($mandiri_account); ?> a.n <?php echo esc_html($mandiri_name); ?></p>
                    <p style="margin: var(--tmp-spacing-sm) 0;"><strong>BNI:</strong> <?php echo esc_html($bni_account); ?> a.n <?php echo esc_html($bni_name); ?></p>
                </div>
            </div>
            
            <div style="margin-bottom: var(--tmp-spacing-xl);">
                <h5><strong>📱 E-Wallet:</strong></h5>
                <div style="background: var(--tmp-color-background); padding: var(--tmp-spacing-lg); border-radius: var(--tmp-radius-md); border-left: 4px solid var(--tmp-color-success);">
                    <p style="margin: var(--tmp-spacing-sm) 0;"><strong>GoPay/OVO/DANA/ShopeePay:</strong> <?php echo esc_html($ewallet_number); ?> a.n <?php echo esc_html($ewallet_name); ?></p>
                </div>
            </div>
            
            <?php if ($payment_notes): ?>
                <div style="background: var(--tmp-color-warning-bg); padding: var(--tmp-spacing-lg); border-radius: var(--tmp-radius-md); border-left: 4px solid var(--tmp-color-warning); margin-bottom: var(--tmp-spacing-lg);">
                    <strong>📝 Note:</strong>
                    <p style="margin: var(--tmp-spacing-sm) 0;"><?php echo nl2br(esc_html($payment_notes)); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="tmpb-next-steps">
            <h5>📋 Next Steps:</h5>
            <ol style="padding-left: var(--tmp-spacing-xl); margin: var(--tmp-spacing-md) 0; line-height: 2; color: var(--tmp-color-text-medium);">
                <?php 
                $steps = explode('\n', $payment_instructions);
                foreach ($steps as $step):
                    if (trim($step)):
                ?>
                    <li><?php echo esc_html(trim($step)); ?></li>
                <?php 
                    endif;
                endforeach; 
                ?>
            </ol>
        </div>
        
        <div class="tmpb-actions" style="display:flex; gap: var(--tmp-spacing-md); flex-wrap: wrap;">
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('my-bookings'))); ?>" class="tmpb-btn tmpb-btn-primary">📋 View My Bookings</a>
            <a href="<?php echo esc_url(get_permalink($tour_id)); ?>" class="tmpb-btn tmpb-btn-secondary">🔙 Back to Tour</a>
        </div>
    </div>
</div>
</div>

<script>
jQuery(document).ready(function($) {
    var $form = $('#tmpb-booking-form');
    var $paxInput = $form.find('input[name="pax"]');
    var $paxDisplay = $('#tmpb-pax-display');
    var $totalAmount = $form.find('.tmpb-total-amount');
    var price = parseInt($totalAmount.data('price')) || 0;
    
    // Calculate total
    $paxInput.on('change input', function() {
        var pax = parseInt($(this).val()) || 1;
        var total = price * pax;
        $paxDisplay.text(pax);
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
                    
                    // Scroll to payment instructions with smooth animation
                    $('html, body').animate({
                        scrollTop: $('#tmpb-payment-instructions').offset().top - 100
                    }, 800);
                } else {
                    alert('❌ Error: ' + response.data.message);
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('❌ Booking failed. Please try again.');
                $submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>
