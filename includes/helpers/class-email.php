<?php
namespace TravelShip\Helpers;

if (!defined('ABSPATH')) exit;

class Email {

    /**
     * Send booking confirmation to user.
     */
    public static function send_booking_confirmation($booking_id) {
        $booking = \TravelShip\DB::get_booking($booking_id);
        if (!$booking) return false;

        $user = get_userdata($booking->user_id);
        if (!$user) return false;

        $subject = sprintf('[TravelShip] Booking Baru #%s', $booking->booking_code);

        $message = self::get_template('booking-confirmation', [
            'user_name'    => $user->display_name,
            'booking_code' => $booking->booking_code,
            'tour_title'   => $booking->tour_title,
            'destination'  => $booking->destination,
            'start_date'   => Utils::format_date($booking->start_date),
            'end_date'     => Utils::format_date($booking->end_date),
            'participants' => $booking->participants,
            'total_price'  => Utils::format_price($booking->total_price),
        ]);

        return self::send($user->user_email, $subject, $message);
    }

    /**
     * Send status update email.
     */
    public static function send_status_update($booking_id, $new_status) {
        $booking = \TravelShip\DB::get_booking($booking_id);
        if (!$booking) return false;

        $user = get_userdata($booking->user_id);
        if (!$user) return false;

        $status_labels = [
            'confirmed' => 'Dikonfirmasi',
            'paid'      => 'Pembayaran Diterima',
            'cancelled' => 'Dibatalkan',
            'completed' => 'Selesai',
        ];
        $status_label = $status_labels[$new_status] ?? ucfirst($new_status);

        $subject = sprintf('[TravelShip] Booking #%s - %s', $booking->booking_code, $status_label);

        $message = self::get_template('status-update', [
            'user_name'    => $user->display_name,
            'booking_code' => $booking->booking_code,
            'tour_title'   => $booking->tour_title,
            'new_status'   => $status_label,
            'dashboard_url' => get_permalink(get_option('travelship_dashboard_page_id')),
        ]);

        return self::send($user->user_email, $subject, $message);
    }

    /**
     * Notify admin of new booking.
     */
    public static function notify_admin_new_booking($booking_id) {
        $booking = \TravelShip\DB::get_booking($booking_id);
        if (!$booking) return false;

        $user = get_userdata($booking->user_id);
        $admin_email = get_option('admin_email');

        $subject = sprintf('[TravelShip] Booking Baru #%s dari %s', $booking->booking_code, $user ? $user->display_name : 'User');

        $message = self::get_template('admin-new-booking', [
            'user_name'    => $user ? $user->display_name : '-',
            'user_email'   => $user ? $user->user_email : '-',
            'booking_code' => $booking->booking_code,
            'tour_title'   => $booking->tour_title,
            'participants' => $booking->participants,
            'total_price'  => Utils::format_price($booking->total_price),
            'admin_url'    => admin_url('admin.php?page=travelship-bookings'),
        ]);

        return self::send($admin_email, $subject, $message);
    }

    /**
     * Core send function.
     */
    private static function send($to, $subject, $message) {
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        return wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Simple HTML email template.
     */
    private static function get_template($template_name, $vars = []) {
        $site_name = get_bloginfo('name');

        $body = '';
        switch ($template_name) {
            case 'booking-confirmation':
                $body = sprintf(
                    '<h2>Halo %s! 👋</h2>
                    <p>Booking kamu berhasil dibuat. Berikut detailnya:</p>
                    <table style="border-collapse:collapse;width:100%%;">
                        <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:600;">Kode Booking</td><td style="padding:8px;border-bottom:1px solid #eee;">%s</td></tr>
                        <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:600;">Tour</td><td style="padding:8px;border-bottom:1px solid #eee;">%s</td></tr>
                        <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:600;">Destinasi</td><td style="padding:8px;border-bottom:1px solid #eee;">%s</td></tr>
                        <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:600;">Tanggal</td><td style="padding:8px;border-bottom:1px solid #eee;">%s - %s</td></tr>
                        <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:600;">Peserta</td><td style="padding:8px;border-bottom:1px solid #eee;">%s orang</td></tr>
                        <tr><td style="padding:8px;font-weight:600;font-size:18px;">Total</td><td style="padding:8px;font-size:18px;color:#10b981;font-weight:700;">%s</td></tr>
                    </table>
                    <p style="margin-top:20px;">Silakan lakukan pembayaran dan upload bukti transfer melalui dashboard kamu.</p>',
                    esc_html($vars['user_name']),
                    esc_html($vars['booking_code']),
                    esc_html($vars['tour_title']),
                    esc_html($vars['destination']),
                    esc_html($vars['start_date']),
                    esc_html($vars['end_date']),
                    esc_html($vars['participants']),
                    esc_html($vars['total_price'])
                );
                break;

            case 'status-update':
                $body = sprintf(
                    '<h2>Halo %s! 👋</h2>
                    <p>Status booking kamu <strong>#%s</strong> untuk tour <strong>%s</strong> telah diperbarui menjadi:</p>
                    <p style="font-size:20px;font-weight:700;color:#3b82f6;">%s</p>
                    <p><a href="%s" style="display:inline-block;padding:12px 24px;background:#3b82f6;color:#fff;text-decoration:none;border-radius:8px;">Lihat di Dashboard</a></p>',
                    esc_html($vars['user_name']),
                    esc_html($vars['booking_code']),
                    esc_html($vars['tour_title']),
                    esc_html($vars['new_status']),
                    esc_url($vars['dashboard_url'])
                );
                break;

            case 'admin-new-booking':
                $body = sprintf(
                    '<h2>Booking Baru! 🎉</h2>
                    <table style="border-collapse:collapse;width:100%%;">
                        <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:600;">User</td><td style="padding:8px;border-bottom:1px solid #eee;">%s (%s)</td></tr>
                        <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:600;">Kode</td><td style="padding:8px;border-bottom:1px solid #eee;">%s</td></tr>
                        <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:600;">Tour</td><td style="padding:8px;border-bottom:1px solid #eee;">%s</td></tr>
                        <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:600;">Peserta</td><td style="padding:8px;border-bottom:1px solid #eee;">%s orang</td></tr>
                        <tr><td style="padding:8px;font-weight:600;">Total</td><td style="padding:8px;font-weight:700;color:#10b981;">%s</td></tr>
                    </table>
                    <p style="margin-top:20px;"><a href="%s" style="display:inline-block;padding:12px 24px;background:#3b82f6;color:#fff;text-decoration:none;border-radius:8px;">Lihat di Admin</a></p>',
                    esc_html($vars['user_name']),
                    esc_html($vars['user_email']),
                    esc_html($vars['booking_code']),
                    esc_html($vars['tour_title']),
                    esc_html($vars['participants']),
                    esc_html($vars['total_price']),
                    esc_url($vars['admin_url'])
                );
                break;
        }

        return sprintf(
            '<div style="max-width:600px;margin:0 auto;font-family:Arial,sans-serif;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                <div style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);padding:24px;text-align:center;">
                    <h1 style="margin:0;color:#fff;font-size:24px;">✈️ %s</h1>
                </div>
                <div style="padding:24px;">%s</div>
                <div style="padding:16px 24px;background:#f9fafb;text-align:center;color:#6b7280;font-size:12px;">
                    &copy; %s %s. All rights reserved.
                </div>
            </div>',
            esc_html($site_name),
            $body,
            date('Y'),
            esc_html($site_name)
        );
    }
}
