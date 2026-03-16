<?php
namespace TravelShip\Helpers;

if (!defined('ABSPATH')) exit;

class Utils {

    /**
     * Format price in Rupiah.
     */
    public static function format_price($amount) {
        return 'Rp ' . number_format((float)$amount, 0, ',', '.');
    }

    /**
     * Format date to Indonesian format.
     */
    public static function format_date($date, $format = 'd M Y') {
        if (empty($date)) return '-';
        return date_i18n($format, strtotime($date));
    }

    /**
     * Format date range.
     */
    public static function format_date_range($start, $end) {
        if (empty($start) || empty($end)) return '-';
        $s = strtotime($start);
        $e = strtotime($end);
        if (date('M Y', $s) === date('M Y', $e)) {
            return date_i18n('d', $s) . ' - ' . date_i18n('d M Y', $e);
        }
        return date_i18n('d M', $s) . ' - ' . date_i18n('d M Y', $e);
    }

    /**
     * Calculate duration in days.
     */
    public static function duration_days($start, $end) {
        if (empty($start) || empty($end)) return 0;
        $diff = strtotime($end) - strtotime($start);
        return max(1, (int) ceil($diff / DAY_IN_SECONDS) + 1);
    }

    /**
     * Get booking status label with color.
     */
    public static function booking_status_badge($status) {
        $statuses = [
            'pending'   => ['label' => 'Menunggu', 'color' => '#f59e0b'],
            'confirmed' => ['label' => 'Dikonfirmasi', 'color' => '#3b82f6'],
            'paid'      => ['label' => 'Sudah Bayar', 'color' => '#10b981'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => '#ef4444'],
            'completed' => ['label' => 'Selesai', 'color' => '#6b7280'],
        ];
        $s = $statuses[$status] ?? ['label' => ucfirst($status), 'color' => '#6b7280'];
        return sprintf(
            '<span class="ts-badge" style="background:%s;color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">%s</span>',
            esc_attr($s['color']),
            esc_html($s['label'])
        );
    }

    /**
     * Get tour status badge.
     */
    public static function tour_status_badge($status) {
        $statuses = [
            'draft'     => ['label' => 'Draft', 'color' => '#6b7280'],
            'published' => ['label' => 'Published', 'color' => '#10b981'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => '#ef4444'],
            'completed' => ['label' => 'Selesai', 'color' => '#3b82f6'],
        ];
        $s = $statuses[$status] ?? ['label' => ucfirst($status), 'color' => '#6b7280'];
        return sprintf(
            '<span class="ts-badge" style="background:%s;color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">%s</span>',
            esc_attr($s['color']),
            esc_html($s['label'])
        );
    }

    /**
     * Get membership level badge.
     */
    public static function membership_badge($level) {
        $levels = [
            'basic'    => ['label' => 'Basic', 'color' => '#6b7280'],
            'silver'   => ['label' => 'Silver', 'color' => '#94a3b8'],
            'gold'     => ['label' => 'Gold', 'color' => '#f59e0b'],
            'platinum' => ['label' => 'Platinum', 'color' => '#8b5cf6'],
        ];
        $l = $levels[$level] ?? ['label' => ucfirst($level), 'color' => '#6b7280'];
        return sprintf(
            '<span class="ts-badge" style="background:%s;color:#fff;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">%s</span>',
            esc_attr($l['color']),
            esc_html($l['label'])
        );
    }

    /**
     * Sanitize and generate slug from title.
     */
    public static function generate_slug($title) {
        return sanitize_title($title) . '-' . wp_rand(1000, 9999);
    }

    /**
     * Get WP attachment image URL.
     */
    public static function get_image_url($attachment_id, $size = 'large') {
        if (!$attachment_id) return TRAVELSHIP_PLUGIN_URL . 'assets/images/placeholder.jpg';
        $url = wp_get_attachment_image_url($attachment_id, $size);
        return $url ?: TRAVELSHIP_PLUGIN_URL . 'assets/images/placeholder.jpg';
    }

    /**
     * Current user is TravelShip member?
     */
    public static function is_member($user_id = null) {
        if (!$user_id) $user_id = get_current_user_id();
        if (!$user_id) return false;
        $user = get_userdata($user_id);
        return $user && in_array('travelship_member', $user->roles);
    }
}
