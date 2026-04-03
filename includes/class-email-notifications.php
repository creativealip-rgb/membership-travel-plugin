<?php
/**
 * Email Notifications System
 * Sends emails to users and admin for booking events
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Email_Notifications {
    
    public function __construct() {
        add_action('tmpb_booking_created', [$this, 'send_user_booking_email'], 10, 3);
        add_action('tmpb_booking_created', [$this, 'send_admin_booking_email'], 10, 3);
        add_action('tmpb_booking_status_changed', [$this, 'send_status_change_email'], 10, 3);
    }
    
    /**
     * Send booking confirmation email to user
     */
    public function send_user_booking_email($booking_id, $user_id, $tour_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) return;
        
        $booking_code = get_post_meta($booking_id, '_booking_code', true);
        $total = get_post_meta($booking_id, '_total_amount', true);
        $tour = get_post($tour_id);
        
        // Get payment settings
        $bca_account = get_option('tmp_bca_account', '1234567890');
        $bca_name = get_option('tmp_bca_name', 'PT Travel');
        $ewallet_number = get_option('tmp_ewallet_number', '0812-3456-7890');
        
        $subject = sprintf('Booking Confirmation - %s', $booking_code);
        
        $message = sprintf('
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%%); color: #fff; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .booking-details { background: #fff; padding: 20px; border: 2px solid #4caf50; border-radius: 8px; margin: 20px 0; }
                .payment-info { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin: 20px 0; }
                .footer { background: #333; color: #fff; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; color: #fff; text-decoration: none; border-radius: 4px; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>✅ Booking Confirmed!</h1>
                    <p>Your tour has been booked successfully</p>
                </div>
                
                <div class="content">
                    <p>Dear %s,</p>
                    
                    <p>Thank you for booking with us! Your booking details:</p>
                    
                    <div class="booking-details">
                        <p><strong>Booking Code:</strong> %s</p>
                        <p><strong>Tour:</strong> %s</p>
                        <p><strong>Total Amount:</strong> Rp %s</p>
                        <p><strong>Status:</strong> Pending Payment</p>
                    </div>
                    
                    <div class="payment-info">
                        <h3>💳 Payment Instructions:</h3>
                        <p><strong>Bank Transfer:</strong></p>
                        <ul>
                            <li>BCA: %s a.n %s</li>
                            <li>Mandiri: 9876543210 a.n %s</li>
                            <li>BNI: 1122334455 a.n %s</li>
                        </ul>
                        <p><strong>E-Wallet:</strong></p>
                        <ul>
                            <li>GoPay/OVO/DANA: %s a.n %s</li>
                        </ul>
                    </div>
                    
                    <p><strong>Next Steps:</strong></p>
                    <ol>
                        <li>Make payment to one of the accounts above</li>
                        <li>Take a screenshot of the payment receipt</li>
                        <li>Go to My Bookings page</li>
                        <li>Click "Upload Payment" for this booking</li>
                        <li>Upload the payment receipt screenshot</li>
                        <li>Wait for admin confirmation (1-2 business days)</li>
                    </ol>
                    
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="%s" class="button">View My Bookings</a>
                    </p>
                    
                    <p>If you have any questions, please contact us.</p>
                    
                    <p>Best regards,<br><strong>PT Travel Team</strong></p>
                </div>
                
                <div class="footer">
                    <p>&copy; %d PT Travel. All rights reserved.</p>
                    <p>This is an automated email, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ',
        $user->display_name,
        $booking_code,
        $tour->post_title,
        number_format($total),
        $bca_account, $bca_name,
        $bca_name,
        $bca_name,
        $ewallet_number, $bca_name,
        get_permalink(get_page_by_path('my-bookings')),
        date('Y')
        );
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: PT Travel <noreply@' . $_SERVER['HTTP_HOST'] . '>',
            'Reply-To: support@' . $_SERVER['HTTP_HOST'],
        ];
        
        wp_mail($user->user_email, $subject, $message, $headers);
    }
    
    /**
     * Send new booking notification to admin
     */
    public function send_admin_booking_email($booking_id, $user_id, $tour_id) {
        $admin_email = get_option('admin_email');
        $user = get_user_by('id', $user_id);
        if (!$user) return;
        
        $booking_code = get_post_meta($booking_id, '_booking_code', true);
        $total = get_post_meta($booking_id, '_total_amount', true);
        $tour = get_post($tour_id);
        $pax = get_post_meta($booking_id, '_pax', true);
        
        $subject = sprintf('🎉 New Booking - %s', $booking_code);
        
        $message = sprintf('
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%%); color: #fff; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .booking-details { background: #fff; padding: 20px; border: 2px solid #11998e; border-radius: 8px; margin: 20px 0; }
                .button { display: inline-block; padding: 12px 30px; background: #11998e; color: #fff; text-decoration: none; border-radius: 4px; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎉 New Booking Received!</h1>
                </div>
                
                <div class="content">
                    <div class="booking-details">
                        <p><strong>Booking Code:</strong> %s</p>
                        <p><strong>Tour:</strong> %s</p>
                        <p><strong>Customer:</strong> %s</p>
                        <p><strong>Email:</strong> %s</p>
                        <p><strong>Phone:</strong> %s</p>
                        <p><strong>Pax:</strong> %s persons</p>
                        <p><strong>Total:</strong> Rp %s</p>
                        <p><strong>Status:</strong> Pending Payment</p>
                    </div>
                    
                    <p><strong>Action Required:</strong></p>
                    <ol>
                        <li>Wait for customer to upload payment proof</li>
                        <li>Verify payment in admin panel</li>
                        <li>Update booking status to "Paid"</li>
                        <li>Send confirmation to customer</li>
                    </ol>
                    
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="%s" class="button">View Booking in Admin</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        ',
        $booking_code,
        $tour->post_title,
        $user->display_name,
        $user->user_email,
        get_post_meta($booking_id, '_customer_phone', true),
        $pax,
        number_format($total),
        admin_url('edit.php?post_type=tour_booking')
        );
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: PT Travel <noreply@' . $_SERVER['HTTP_HOST'] . '>',
        ];
        
        wp_mail($admin_email, $subject, $message, $headers);
    }
    
    /**
     * Send status change notification to user
     */
    public function send_status_change_email($booking_id, $new_status) {
        $user_id = get_post_meta($booking_id, '_user_id', true);
        $user = get_user_by('id', $user_id);
        if (!$user) return;
        
        $booking_code = get_post_meta($booking_id, '_booking_code', true);
        $tour_id = get_post_meta($booking_id, '_tour_id', true);
        $tour = get_post($tour_id);
        
        $status_messages = [
            'paid' => ['✅ Payment Confirmed', 'Your payment has been verified and confirmed.'],
            'confirmed' => ['✓ Booking Confirmed', 'Your booking is confirmed! Get ready for your trip.'],
            'cancelled' => ['❌ Booking Cancelled', 'Your booking has been cancelled.'],
            'completed' => ['✔ Tour Completed', 'Thank you for traveling with us!'],
        ];
        
        $status_info = $status_messages[$new_status] ?? ['Status Updated', 'Your booking status has been updated.'];
        
        $subject = sprintf('%s - %s', $status_info[0], $booking_code);
        
        $message = sprintf('
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%%, #764ba2 100%%%); color: #fff; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .status-box { background: #fff; padding: 20px; border: 2px solid #667eea; border-radius: 8px; margin: 20px 0; text-align: center; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; color: #fff; text-decoration: none; border-radius: 4px; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>%s</h1>
                </div>
                
                <div class="content">
                    <p>Dear %s,</p>
                    
                    <p>%s</p>
                    
                    <div class="status-box">
                        <p><strong>Booking Code:</strong> %s</p>
                        <p><strong>Tour:</strong> %s</p>
                        <p><strong>New Status:</strong> %s</p>
                    </div>
                    
                    <p style="text-align: center; margin: 30px 0;">
                        <a href="%s" class="button">View My Bookings</a>
                    </p>
                    
                    <p>If you have any questions, please contact us.</p>
                    
                    <p>Best regards,<br><strong>PT Travel Team</strong></p>
                </div>
            </div>
        </body>
        </html>
        ',
        $status_info[0],
        $user->display_name,
        $status_info[1],
        $booking_code,
        $tour->post_title,
        ucfirst(str_replace('_', ' ', $new_status)),
        get_permalink(get_page_by_path('my-bookings'))
        );
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: PT Travel <noreply@' . $_SERVER['HTTP_HOST'] . '>',
        ];
        
        wp_mail($user->user_email, $subject, $message, $headers);
    }
}

new TMP_Email_Notifications();
