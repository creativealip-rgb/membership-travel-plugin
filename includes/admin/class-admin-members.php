<?php
namespace TravelShip\Admin;

if (!defined('ABSPATH')) exit;

use TravelShip\DB;
use TravelShip\Helpers\Utils;

class AdminMembers {

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
        $args = [
            'membership_level' => sanitize_text_field($_GET['level'] ?? ''),
            'search'           => sanitize_text_field($_GET['s'] ?? ''),
            'page'             => max(1, (int)($_GET['paged'] ?? 1)),
            'per_page'         => 15,
        ];
        $result = DB::get_members($args);

        include TRAVELSHIP_PLUGIN_DIR . 'templates/admin/members-list.php';
    }

    private function render_detail() {
        $id = (int)($_GET['id'] ?? 0);
        $member = DB::get_member($id);

        if (!$member) {
            echo '<div class="notice notice-error"><p>Member tidak ditemukan.</p></div>';
            return;
        }

        // Handle membership level update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce($_POST['_travelship_nonce'] ?? '', 'travelship_update_member')) {
            $update_data = [];
            if (isset($_POST['membership_level'])) {
                $update_data['membership_level'] = sanitize_text_field($_POST['membership_level']);
            }
            if (isset($_POST['points'])) {
                $update_data['points'] = (int) $_POST['points'];
            }
            if (!empty($update_data)) {
                DB::update_member($id, $update_data);
                $member = DB::get_member($id);
                echo '<div class="notice notice-success"><p>Data member berhasil diperbarui.</p></div>';
            }
        }

        // Get member's booking history
        $bookings = DB::get_bookings(['user_id' => $member->user_id, 'per_page' => 100]);

        include TRAVELSHIP_PLUGIN_DIR . 'templates/admin/members-detail.php';
    }
}
