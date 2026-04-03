<?php
/**
 * Booking Form Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
?>

<div class="tmpb-booking-form" data-tour-id="<?php echo esc_attr($tour_id); ?>">
    <h3><?php esc_html_e('Book This Tour', 'travel-membership-pro'); ?></h3>
    
    <form id="tmpb-booking-form">
        <input type="hidden" name="tour_id" value="<?php echo esc_attr($tour_id); ?>">
        
        <div class="tmpb-form-group">
            <label><?php esc_html_e('Number of Pax', 'travel-membership-pro'); ?> *</label>
            <input type="number" name="pax" min="1" value="1" required>
        </div>
        
        <div class="tmpb-form-group">
            <label><?php esc_html_e('Travel Date', 'travel-membership-pro'); ?> *</label>
            <input type="date" name="travel_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
        </div>
        
        <div class="tmpb-form-row">
            <div class="tmpb-form-group">
                <label><?php esc_html_e('Full Name', 'travel-membership-pro'); ?> *</label>
                <input type="text" name="name" value="<?php echo esc_attr($user->display_name); ?>" required>
            </div>
            
            <div class="tmpb-form-group">
                <label><?php esc_html_e('Email', 'travel-membership-pro'); ?> *</label>
                <input type="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" required>
            </div>
        </div>
        
        <div class="tmpb-form-group">
            <label><?php esc_html_e('Phone Number', 'travel-membership-pro'); ?> *</label>
            <input type="tel" name="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone_number', true)); ?>" required>
        </div>
        
        <div class="tmpb-form-group">
            <label><?php esc_html_e('Notes / Special Requests', 'travel-membership-pro'); ?></label>
            <textarea name="notes" rows="3"></textarea>
        </div>
        
        <div class="tmpb-form-group">
            <label><?php esc_html_e('Payment Method', 'travel-membership-pro'); ?> *</label>
            <select name="payment_method" required>
                <option value=""><?php esc_html_e('Select Payment Method', 'travel-membership-pro'); ?></option>
                <option value="bank_transfer"><?php esc_html_e('Bank Transfer', 'travel-membership-pro'); ?></option>
                <option value="gopay">GoPay</option>
                <option value="ovo">OVO</option>
                <option value="dana">DANA</option>
            </select>
        </div>
        
        <div class="tmpb-booking-total">
            <strong><?php esc_html_e('Total:', 'travel-membership-pro'); ?></strong>
            <span class="tmpb-total-amount" data-price="<?php echo absint(get_post_meta($tour_id, 'price', true)); ?>">
                Rp 0
            </span>
        </div>
        
        <button type="submit" class="tmpb-btn tmpb-btn-primary tmpb-btn-block">
            <?php esc_html_e('Book Now', 'travel-membership-pro'); ?>
        </button>
    </form>
    
    <div class="tmpb-booking-success" style="display:none;">
        <p>✅ <?php esc_html_e('Booking successful!', 'travel-membership-pro'); ?></p>
        <p><?php esc_html_e('Booking Code:', 'travel-membership-pro'); ?> <strong class="tmpb-booking-code"></strong></p>
        <p><?php esc_html_e('We will contact you soon for payment confirmation.', 'travel-membership-pro'); ?></p>
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
    });
    
    // Submit form
    $form.on('submit', function(e) {
        e.preventDefault();
        
        var $submitBtn = $form.find('button[type="submit"]');
        $submitBtn.prop('disabled', true).text('Processing...');
        
        var formData = new FormData(this);
        formData.append('action', 'tmpb_create_booking');
        formData.append('nonce', tmpbAjax.nonce);
        
        $.ajax({
            url: tmpbAjax.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $form.hide();
                    $('.tmpb-booking-success').show();
                    setTimeout(function() { window.location.href = '/checkout?booking_id=' + response.data.booking_id + ''&code=' + response.data.booking_code;code=' + response.data.booking_code; }, 1500);
                } else {
                    alert('Error: ' + response.data.message);
                    $submitBtn.prop('disabled', false).text('Book Now');
                }
            },
            error: function() {
                alert('Booking failed. Please try again.');
                $submitBtn.prop('disabled', false).text('Book Now');
            }
        });
    });
});
</script>
