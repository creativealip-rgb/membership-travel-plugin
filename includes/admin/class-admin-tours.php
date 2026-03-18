<?php
namespace TravelShip\Admin;

if (!defined('ABSPATH')) exit;

use TravelShip\DB;
use TravelShip\Helpers\Utils;

class AdminTours {

    public function render() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

        switch ($action) {
            case 'new':
            case 'edit':
                $this->render_form();
                break;
            default:
                $this->render_list();
                break;
        }
    }

    private function render_list() {
        // Handle delete
        if (isset($_GET['delete']) && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'travelship_delete_tour')) {
            DB::delete_tour((int) $_GET['delete']);
            echo '<div class="notice notice-success"><p>Tour berhasil dihapus.</p></div>';
        }

        // Handle duplicate
        if (isset($_GET['duplicate']) && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'travelship_duplicate_tour')) {
            $source = DB::get_tour((int) $_GET['duplicate']);
            if ($source) {
                $new_data = (array) $source;
                unset($new_data['id']);
                $new_data['title'] = $source->title . ' (Copy)';
                $new_data['slug'] = Utils::generate_slug($new_data['title']);
                $new_data['status'] = 'draft';
                DB::insert_tour($new_data);
                echo '<div class="notice notice-success"><p>Tour berhasil diduplikat.</p></div>';
            }
        }

        $args = [
            'status'  => sanitize_text_field($_GET['status'] ?? ''),
            'search'  => sanitize_text_field($_GET['s'] ?? ''),
            'page'    => max(1, (int)($_GET['paged'] ?? 1)),
            'per_page' => 15,
        ];
        $result = DB::get_tours($args);

        include TRAVELSHIP_PLUGIN_DIR . 'templates/admin/tours-list.php';
    }

    private function render_form() {
        // Handle save
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce($_POST['_travelship_nonce'] ?? '', 'travelship_save_tour')) {
            $data = $this->sanitize_tour_data($_POST);

            if (!empty($_POST['tour_id'])) {
                DB::update_tour((int) $_POST['tour_id'], $data);
                $tour_id = (int) $_POST['tour_id'];
                echo '<div class="notice notice-success"><p>Tour berhasil diperbarui.</p></div>';
            } else {
                $data['slug'] = Utils::generate_slug($data['title']);
                $data['created_by'] = get_current_user_id();
                $tour_id = DB::insert_tour($data);
                echo '<div class="notice notice-success"><p>Tour berhasil dibuat.</p></div>';
            }
        }

        $tour = null;
        if (isset($_GET['id'])) {
            $tour = DB::get_tour((int) $_GET['id']);
        } elseif (isset($tour_id)) {
            $tour = DB::get_tour($tour_id);
        }

        include TRAVELSHIP_PLUGIN_DIR . 'templates/admin/tours-form.php';
    }

    private function sanitize_tour_data($post) {
        return [
            'title'            => sanitize_text_field($post['title'] ?? ''),
            'description'      => wp_kses_post($post['description'] ?? ''),
            'destination'      => sanitize_text_field($post['destination'] ?? ''),
            'thumbnail_id'     => (int)($post['thumbnail_id'] ?? 0),
            'gallery_ids'      => sanitize_text_field($post['gallery_ids'] ?? ''),
            'price'            => floatval($post['price'] ?? 0),
            'max_participants' => (int)($post['max_participants'] ?? 0),
            'start_date'       => sanitize_text_field($post['start_date'] ?? ''),
            'end_date'         => sanitize_text_field($post['end_date'] ?? ''),
            'itinerary'        => wp_kses_post($post['itinerary'] ?? ''),
            'includes'         => wp_kses_post($post['includes'] ?? ''),
            'excludes'         => wp_kses_post($post['excludes'] ?? ''),
            'terms'            => wp_kses_post($post['terms'] ?? ''),
            'status'           => sanitize_text_field($post['status'] ?? 'draft'),
            'is_featured'      => isset($post['is_featured']) ? 1 : 0,
        ];
    }
}
