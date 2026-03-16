<?php
namespace TravelShip\Api;

if (!defined('ABSPATH')) exit;

use TravelShip\DB;
use TravelShip\Helpers\Utils;
use TravelShip\Helpers\Email;

class RestApi {

    const NAMESPACE = 'travelship/v1';

    public function register_routes() {
        // ─── Tours (Public) ────────────────────
        register_rest_route(self::NAMESPACE, '/tours', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_tours'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/tours/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_tour'],
            'permission_callback' => '__return_true',
        ]);

        // ─── Profile (Authenticated) ──────────
        register_rest_route(self::NAMESPACE, '/profile', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_profile'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        register_rest_route(self::NAMESPACE, '/profile', [
            'methods'             => 'PUT',
            'callback'            => [$this, 'update_profile'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        // ─── Bookings (Authenticated) ─────────
        register_rest_route(self::NAMESPACE, '/bookings', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_bookings'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings', [
            'methods'             => 'POST',
            'callback'            => [$this, 'create_booking'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_booking_detail'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings/(?P<id>\d+)/cancel', [
            'methods'             => 'POST',
            'callback'            => [$this, 'cancel_booking'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings/(?P<id>\d+)/upload-proof', [
            'methods'             => 'POST',
            'callback'            => [$this, 'upload_payment_proof'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        // ─── Reviews (Authenticated) ──────────
        register_rest_route(self::NAMESPACE, '/reviews', [
            'methods'             => 'POST',
            'callback'            => [$this, 'create_review'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        register_rest_route(self::NAMESPACE, '/tours/(?P<id>\d+)/reviews', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_tour_reviews'],
            'permission_callback' => '__return_true',
        ]);

        // ─── Dashboard Stats (Authenticated) ──
        register_rest_route(self::NAMESPACE, '/dashboard', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_user_dashboard'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        // ─── Settings (Authenticated) ─────────
        register_rest_route(self::NAMESPACE, '/settings', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_user_settings'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        register_rest_route(self::NAMESPACE, '/settings', [
            'methods'             => 'PUT',
            'callback'            => [$this, 'update_user_settings'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);

        // ─── Change Password ──────────────────
        register_rest_route(self::NAMESPACE, '/change-password', [
            'methods'             => 'POST',
            'callback'            => [$this, 'change_password'],
            'permission_callback' => [$this, 'is_logged_in'],
        ]);
    }

    // ─── Permission callback ──────────────────
    public function is_logged_in() {
        return is_user_logged_in();
    }

    // ─── Tours ────────────────────────────────

    public function get_tours($request) {
        $args = [
            'status'      => 'published',
            'search'      => $request->get_param('search') ?: '',
            'destination' => $request->get_param('destination') ?: '',
            'per_page'    => min(50, (int)($request->get_param('per_page') ?: 12)),
            'page'        => max(1, (int)($request->get_param('page') ?: 1)),
            'orderby'     => $request->get_param('orderby') ?: 'start_date',
            'order'       => $request->get_param('order') ?: 'ASC',
        ];

        $result = DB::get_tours($args);
        $items = array_map(function ($tour) {
            return $this->format_tour($tour);
        }, $result['items']);

        return rest_ensure_response([
            'items'       => $items,
            'total'       => $result['total'],
            'total_pages' => $result['total_pages'],
            'page'        => $result['page'],
        ]);
    }

    public function get_tour($request) {
        $tour = DB::get_tour((int) $request['id']);
        if (!$tour || $tour->status !== 'published') {
            return new \WP_Error('not_found', 'Tour tidak ditemukan', ['status' => 404]);
        }
        return rest_ensure_response($this->format_tour($tour, true));
    }

    private function format_tour($tour, $full = false) {
        $booked = DB::get_tour_booked_count($tour->id);
        $data = [
            'id'               => (int) $tour->id,
            'title'            => $tour->title,
            'slug'             => $tour->slug,
            'destination'      => $tour->destination,
            'thumbnail'        => Utils::get_image_url($tour->thumbnail_id, 'large'),
            'price'            => (float) $tour->price,
            'price_formatted'  => Utils::format_price($tour->price),
            'max_participants' => (int) $tour->max_participants,
            'booked'           => $booked,
            'available'        => max(0, $tour->max_participants - $booked),
            'start_date'       => $tour->start_date,
            'end_date'         => $tour->end_date,
            'date_range'       => Utils::format_date_range($tour->start_date, $tour->end_date),
            'duration'         => Utils::duration_days($tour->start_date, $tour->end_date),
            'status'           => $tour->status,
            'avg_rating'       => round((float) DB::get_tour_avg_rating($tour->id), 1),
        ];

        if ($full) {
            $gallery = !empty($tour->gallery_ids) ? json_decode($tour->gallery_ids, true) : [];
            $gallery_urls = [];
            if (is_array($gallery)) {
                foreach ($gallery as $gid) {
                    $gallery_urls[] = Utils::get_image_url($gid, 'large');
                }
            }
            $data['description'] = $tour->description;
            $data['itinerary']   = $tour->itinerary;
            $data['includes']    = $tour->includes;
            $data['excludes']    = $tour->excludes;
            $data['terms']       = $tour->terms;
            $data['gallery']     = $gallery_urls;
        }

        return $data;
    }

    // ─── Profile ──────────────────────────────

    public function get_profile($request) {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $member = DB::get_member_by_user_id($user_id);

        if (!$member) {
            DB::insert_member(['user_id' => $user_id, 'membership_level' => 'basic', 'points' => 0]);
            $member = DB::get_member_by_user_id($user_id);
        }

        return rest_ensure_response([
            'id'                => $user_id,
            'display_name'      => $user->display_name,
            'email'             => $user->user_email,
            'avatar'            => get_avatar_url($user_id, ['size' => 200]),
            'phone'             => $member->phone ?? '',
            'address'           => $member->address ?? '',
            'emergency_contact' => $member->emergency_contact ?? '',
            'emergency_phone'   => $member->emergency_phone ?? '',
            'id_number'         => $member->id_number ?? '',
            'membership_level'  => $member->membership_level ?? 'basic',
            'points'            => (int) ($member->points ?? 0),
            'joined_at'         => $member->joined_at ?? '',
        ]);
    }

    public function update_profile($request) {
        $user_id = get_current_user_id();
        $body = $request->get_json_params();

        // Update WP user
        if (isset($body['display_name'])) {
            wp_update_user(['ID' => $user_id, 'display_name' => sanitize_text_field($body['display_name'])]);
        }

        // Update member table
        $member_data = [];
        $fields = ['phone', 'address', 'emergency_contact', 'emergency_phone', 'id_number'];
        foreach ($fields as $f) {
            if (isset($body[$f])) {
                $member_data[$f] = sanitize_text_field($body[$f]);
            }
        }

        if (!empty($member_data)) {
            $existing = DB::get_member_by_user_id($user_id);
            if ($existing) {
                DB::update_member_by_user_id($user_id, $member_data);
            } else {
                $member_data['user_id'] = $user_id;
                $member_data['membership_level'] = 'basic';
                DB::insert_member($member_data);
            }
        }

        return $this->get_profile($request);
    }

    // ─── Bookings ─────────────────────────────

    public function get_bookings($request) {
        $user_id = get_current_user_id();
        $status = $request->get_param('status') ?: '';
        $type = $request->get_param('type') ?: 'all'; // 'upcoming', 'history', 'all'

        $args = [
            'user_id'  => $user_id,
            'status'   => $status,
            'per_page' => min(50, (int)($request->get_param('per_page') ?: 20)),
            'page'     => max(1, (int)($request->get_param('page') ?: 1)),
        ];

        $result = DB::get_bookings($args);
        $items = [];

        foreach ($result['items'] as $b) {
            $item = $this->format_booking($b);

            // Filter by type
            if ($type === 'upcoming' && in_array($b->status, ['cancelled', 'completed'])) continue;
            if ($type === 'history' && !in_array($b->status, ['cancelled', 'completed'])) continue;

            $items[] = $item;
        }

        return rest_ensure_response([
            'items'       => $items,
            'total'       => $result['total'],
            'total_pages' => $result['total_pages'],
            'page'        => $result['page'],
        ]);
    }

    public function create_booking($request) {
        $user_id = get_current_user_id();
        $body = $request->get_json_params();

        $tour_id = (int) ($body['tour_id'] ?? 0);
        $participants = max(1, (int) ($body['participants'] ?? 1));

        $tour = DB::get_tour($tour_id);
        if (!$tour || $tour->status !== 'published') {
            return new \WP_Error('invalid_tour', 'Tour tidak valid', ['status' => 400]);
        }

        // Check availability
        $booked = DB::get_tour_booked_count($tour_id);
        if (($booked + $participants) > $tour->max_participants) {
            return new \WP_Error('no_slot', 'Kuota tidak mencukupi', ['status' => 400]);
        }

        $total_price = $tour->price * $participants;

        $booking_id = DB::insert_booking([
            'user_id'        => $user_id,
            'tour_id'        => $tour_id,
            'participants'   => $participants,
            'total_price'    => $total_price,
            'status'         => 'pending',
            'notes'          => sanitize_textarea_field($body['notes'] ?? ''),
            'payment_method' => sanitize_text_field($body['payment_method'] ?? 'transfer'),
        ]);

        // Send emails
        Email::send_booking_confirmation($booking_id);
        Email::notify_admin_new_booking($booking_id);

        $booking = DB::get_booking($booking_id);
        return rest_ensure_response($this->format_booking($booking));
    }

    public function get_booking_detail($request) {
        $user_id = get_current_user_id();
        $booking = DB::get_booking((int) $request['id']);

        if (!$booking || (int) $booking->user_id !== $user_id) {
            return new \WP_Error('not_found', 'Booking tidak ditemukan', ['status' => 404]);
        }

        return rest_ensure_response($this->format_booking($booking, true));
    }

    public function cancel_booking($request) {
        $user_id = get_current_user_id();
        $booking = DB::get_booking((int) $request['id']);

        if (!$booking || (int) $booking->user_id !== $user_id) {
            return new \WP_Error('not_found', 'Booking tidak ditemukan', ['status' => 404]);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return new \WP_Error('cannot_cancel', 'Booking tidak bisa dibatalkan', ['status' => 400]);
        }

        DB::update_booking($booking->id, ['status' => 'cancelled']);
        return rest_ensure_response(['success' => true, 'message' => 'Booking berhasil dibatalkan']);
    }

    public function upload_payment_proof($request) {
        $user_id = get_current_user_id();
        $booking = DB::get_booking((int) $request['id']);

        if (!$booking || (int) $booking->user_id !== $user_id) {
            return new \WP_Error('not_found', 'Booking tidak ditemukan', ['status' => 404]);
        }

        $files = $request->get_file_params();
        if (empty($files['file'])) {
            return new \WP_Error('no_file', 'File tidak ditemukan', ['status' => 400]);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload('file', 0);
        if (is_wp_error($attachment_id)) {
            return new \WP_Error('upload_failed', 'Gagal upload file', ['status' => 500]);
        }

        DB::update_booking($booking->id, ['payment_proof' => $attachment_id]);

        return rest_ensure_response([
            'success' => true,
            'message' => 'Bukti pembayaran berhasil diupload',
            'proof_url' => wp_get_attachment_url($attachment_id),
        ]);
    }

    private function format_booking($b, $full = false) {
        $tour = DB::get_tour($b->tour_id);
        $data = [
            'id'              => (int) $b->id,
            'booking_code'    => $b->booking_code,
            'tour_id'         => (int) $b->tour_id,
            'tour_title'      => $b->tour_title ?? ($tour ? $tour->title : ''),
            'destination'     => $b->destination ?? ($tour ? $tour->destination : ''),
            'thumbnail'       => $tour ? Utils::get_image_url($tour->thumbnail_id) : '',
            'start_date'      => $b->start_date ?? ($tour ? $tour->start_date : ''),
            'end_date'        => $b->end_date ?? ($tour ? $tour->end_date : ''),
            'date_range'      => Utils::format_date_range($b->start_date ?? '', $b->end_date ?? ''),
            'participants'    => (int) $b->participants,
            'total_price'     => (float) $b->total_price,
            'price_formatted' => Utils::format_price($b->total_price),
            'status'          => $b->status,
            'payment_method'  => $b->payment_method ?? '',
            'created_at'      => $b->created_at,
            'has_proof'       => !empty($b->payment_proof) && $b->payment_proof > 0,
        ];

        if ($full) {
            $data['notes'] = $b->notes ?? '';
            $data['payment_proof_url'] = $b->payment_proof ? wp_get_attachment_url($b->payment_proof) : '';
            $data['paid_at'] = $b->paid_at ?? '';
            if ($tour) {
                $data['tour_price'] = (float) $tour->price;
                $data['tour_price_formatted'] = Utils::format_price($tour->price);
                $data['itinerary'] = $tour->itinerary;
                $data['includes'] = $tour->includes;
                $data['excludes'] = $tour->excludes;
            }
        }

        return $data;
    }

    // ─── Reviews ──────────────────────────────

    public function get_tour_reviews($request) {
        $reviews = DB::get_reviews_by_tour((int) $request['id']);
        $items = array_map(function ($r) {
            return [
                'id'           => (int) $r->id,
                'user_name'    => $r->display_name,
                'avatar'       => get_avatar_url($r->user_id, ['size' => 80]),
                'rating'       => (int) $r->rating,
                'review'       => $r->review,
                'created_at'   => $r->created_at,
            ];
        }, $reviews);

        return rest_ensure_response($items);
    }

    public function create_review($request) {
        $user_id = get_current_user_id();
        $body = $request->get_json_params();

        $tour_id = (int) ($body['tour_id'] ?? 0);
        $rating = max(1, min(5, (int) ($body['rating'] ?? 5)));
        $review = sanitize_textarea_field($body['review'] ?? '');

        $tour = DB::get_tour($tour_id);
        if (!$tour) {
            return new \WP_Error('invalid_tour', 'Tour tidak valid', ['status' => 400]);
        }

        $id = DB::insert_review([
            'user_id' => $user_id,
            'tour_id' => $tour_id,
            'rating'  => $rating,
            'review'  => $review,
        ]);

        return rest_ensure_response(['success' => true, 'id' => $id]);
    }

    // ─── User Dashboard Stats ─────────────────

    public function get_user_dashboard($request) {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $member = DB::get_member_by_user_id($user_id);

        $all_bookings = DB::get_bookings(['user_id' => $user_id, 'per_page' => 999]);
        $total_trips = 0;
        $upcoming_trips = 0;
        $total_spending = 0;
        $next_trip = null;

        foreach ($all_bookings['items'] as $b) {
            if ($b->status === 'completed') $total_trips++;
            if (in_array($b->status, ['pending', 'confirmed', 'paid'])) {
                $upcoming_trips++;
                if (!$next_trip && $b->start_date && strtotime($b->start_date) > time()) {
                    $next_trip = $this->format_booking($b);
                }
            }
            if (in_array($b->status, ['paid', 'completed'])) {
                $total_spending += $b->total_price;
            }
        }

        return rest_ensure_response([
            'user' => [
                'display_name'     => $user->display_name,
                'email'            => $user->user_email,
                'avatar'           => get_avatar_url($user_id, ['size' => 200]),
                'membership_level' => $member->membership_level ?? 'basic',
                'points'           => (int) ($member->points ?? 0),
            ],
            'stats' => [
                'total_trips'      => $total_trips,
                'upcoming_trips'   => $upcoming_trips,
                'total_spending'   => Utils::format_price($total_spending),
            ],
            'next_trip' => $next_trip,
        ]);
    }

    // ─── User Settings ────────────────────────

    public function get_user_settings($request) {
        $user_id = get_current_user_id();
        $settings = get_user_meta($user_id, 'travelship_settings', true) ?: [];

        return rest_ensure_response([
            'email_notifications' => $settings['email_notifications'] ?? true,
            'profile_visible'     => $settings['profile_visible'] ?? true,
        ]);
    }

    public function update_user_settings($request) {
        $user_id = get_current_user_id();
        $body = $request->get_json_params();

        $settings = [
            'email_notifications' => isset($body['email_notifications']) ? (bool) $body['email_notifications'] : true,
            'profile_visible'     => isset($body['profile_visible']) ? (bool) $body['profile_visible'] : true,
        ];

        update_user_meta($user_id, 'travelship_settings', $settings);
        return rest_ensure_response($settings);
    }

    // ─── Change Password ──────────────────────

    public function change_password($request) {
        $user_id = get_current_user_id();
        $body = $request->get_json_params();
        $user = get_userdata($user_id);

        if (empty($body['current_password']) || empty($body['new_password'])) {
            return new \WP_Error('invalid', 'Password tidak boleh kosong', ['status' => 400]);
        }

        if (!wp_check_password($body['current_password'], $user->user_pass, $user_id)) {
            return new \WP_Error('wrong_password', 'Password lama salah', ['status' => 400]);
        }

        if (strlen($body['new_password']) < 6) {
            return new \WP_Error('weak_password', 'Password baru minimal 6 karakter', ['status' => 400]);
        }

        wp_set_password($body['new_password'], $user_id);
        return rest_ensure_response(['success' => true, 'message' => 'Password berhasil diubah']);
    }
}
