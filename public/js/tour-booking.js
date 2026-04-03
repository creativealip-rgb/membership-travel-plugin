/**
 * Tour Booking JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initBookingForm();
        initMyBookings();
        initPaymentUpload();
    });
    
    /**
     * Initialize booking forms
     */
    function initBookingForm() {
        var $forms = $('.tmpb-booking-form');
        if ($forms.length === 0) return;
        
        $forms.each(function() {
            var $form = $(this);
            var $paxInput = $form.find('input[name="pax"]');
            var $totalAmount = $form.find('.tmpb-total-amount');
            var price = parseInt($totalAmount.data('price')) || 0;
            
            // Calculate total on pax change
            $paxInput.on('change', function() {
                var pax = parseInt($(this).val()) || 1;
                var total = price * pax;
                $totalAmount.text('Rp ' + total.toLocaleString('id-ID'));
            }).trigger('change');
            
            // Submit form
            $form.find('form').on('submit', function(e) {
                e.preventDefault();
                
                var $submitBtn = $form.find('button[type="submit"]');
                var originalText = $submitBtn.text();
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
                            $form.next('.tmpb-booking-success').show();
                            $form.next('.tmpb-booking-success').find('.tmpb-booking-code').text(response.data.booking_code);
                        } else {
                            alert(tmpbAjax.i18n.bookingError + ': ' + response.data.message);
                            $submitBtn.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function() {
                        alert(tmpbAjax.i18n.bookingError);
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
    }
    
    /**
     * Initialize my bookings
     */
    function initMyBookings() {
        // Cancel booking
        $(document).on('click', '.tmpb-cancel-booking', function() {
            if (!confirm(tmpbAjax.i18n.confirmCancel)) return;
            
            var $btn = $(this);
            var bookingId = $btn.data('booking-id');
            
            $.ajax({
                url: tmpbAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tmpb_cancel_booking',
                    nonce: tmpbAjax.nonce,
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
        $(document).on('click', '.tmpb-upload-payment', function() {
            var bookingId = $(this).data('booking-id');
            showPaymentUploadModal(bookingId);
        });
    }
    
    /**
     * Show payment upload modal
     */
    function showPaymentUploadModal(bookingId) {
        var html = `
            <div id="tmpb-payment-modal" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:999999;">
                <div style="background:#fff; padding:30px; border-radius:8px; max-width:500px; width:90%;">
                    <h3>Upload Payment Proof</h3>
                    <form id="tmpb-payment-form">
                        <input type="hidden" name="booking_id" value="${bookingId}">
                        
                        <div style="margin:15px 0;">
                            <label style="display:block; margin-bottom:5px;">Payment Method</label>
                            <select name="method" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="gopay">GoPay</option>
                                <option value="ovo">OVO</option>
                                <option value="dana">DANA</option>
                            </select>
                        </div>
                        
                        <div style="margin:15px 0;">
                            <label style="display:block; margin-bottom:5px;">Payment Proof</label>
                            <input type="file" name="payment_proof" accept="image/*" required style="width:100%;">
                            <small style="color:#666;">Upload screenshot of transfer receipt</small>
                        </div>
                        
                        <div style="display:flex; gap:10px; margin-top:20px;">
                            <button type="submit" class="tmpb-btn tmpb-btn-primary" style="flex:1;">Upload</button>
                            <button type="button" class="tmpb-btn tmpb-btn-secondary" onclick="document.getElementById('tmpb-payment-modal').remove()" style="flex:1;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        $('body').append(html);
        
        $('#tmpb-payment-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            formData.append('action', 'tmpb_upload_payment');
            formData.append('nonce', tmpbAjax.nonce);
            
            $.ajax({
                url: tmpbAjax.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Payment proof uploaded! We will verify it soon.');
                        $('#tmpb-payment-modal').remove();
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Upload failed. Please try again.');
                }
            });
        });
    }
    
    /**
     * Initialize payment upload
     */
    function initPaymentUpload() {
        // Handled in showPaymentUploadModal
    }
    
})(jQuery);
