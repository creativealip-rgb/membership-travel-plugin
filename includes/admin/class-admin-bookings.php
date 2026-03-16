<?php
namespace TravelShip\Admin;

if (!defined('ABSPATH')) exit;

use TravelShip\DB;
use TravelShip\Helpers\Utils;
use TravelShip\Helpers\Email;

class AdminBookings {

    public function render() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

        switch ($action) {
            case 'view':
                $this->render_detail();
                break;
            default:
                $this->render_list();
                break;
        }
    }

    private function render_list() {
        // Handle status update
        if (isset($_POST['update_status']) && wp_verify_nonce($_POST['_travelship_nonce'] ?? '', 'travelship_update_booking_status')) {
            $booking_id = (int) $_POST['booking_id'];
            $new_status = sanitize_text_field($_POST['new_status']);
            $allowed = ['pending','confirmed','paid','cancelled','completed'];

            if (in_array($new_status, $allowed)) {
                DB::update_booking($booking_id, ['status' => $new_status]);
                if ($new_status === 'paid') {
                    DB::update_booking($booking_id, ['paid_at' => current_time('mysql')]);
                }
                Email::send_status_update($booking_id, $new_status);
                echo '<div class="notice notice-success"><p>Status booking berhasil diperbarui.</p></div>';
            }
        }

        $args = [
            'status'   => sanitize_text_field($_GET['status'] ?? ''),
            'tour_id'  => (int)($_GET['tour_id'] ?? 0),
            'search'   => sanitize_text_field($_GET['s'] ?? ''),
            'page'     => max(1, (int)($_GET['paged'] ?? 1)),
            'per_page' => 15,
        ];
        $result = DB::get_bookings($args);

        include TRAVELSHIP_PLUGIN_DIR . 'templates/admin/bookings-list.php';
    }

    private function render_detail() {
        $id = (int)($_GET['id'] ?? 0);
        $booking = DB::get_booking($id);

        if (!$booking) {
            echo '<div class="notice notice-error"><p>Booking tidak ditemukan.</p></div>';
            return;
        }

        $user = get_userdata($booking->user_id);
        $member = DB::get_member_by_user_id($booking->user_id);

        include TRAVELSHIP_PLUGIN_DIR . 'templates/admin/bookings-detail.php';
    }
}
