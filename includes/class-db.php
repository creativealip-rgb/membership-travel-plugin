<?php
namespace TravelShip;

if (!defined('ABSPATH')) exit;

/**
 * Database helper class — wraps $wpdb for TravelShip custom tables.
 */
class DB {

    /**
     * Get the full table name with WP prefix.
     */
    public static function table($name) {
        global $wpdb;
        return $wpdb->prefix . 'travelship_' . $name;
    }

    // ─── TOURS ───────────────────────────────────────────

    public static function get_tours($args = []) {
        global $wpdb;
        $table = self::table('tours');
        $defaults = [
            'status'      => '',
            'destination' => '',
            'search'      => '',
            'orderby'     => 'start_date',
            'order'       => 'DESC',
            'per_page'    => 10,
            'page'        => 1,
        ];
        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $values = [];

        if ($args['status']) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }
        if ($args['destination']) {
            $where[] = 'destination LIKE %s';
            $values[] = '%' . $wpdb->esc_like($args['destination']) . '%';
        }
        if ($args['search']) {
            $where[] = '(title LIKE %s OR destination LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search;
            $values[] = $search;
        }

        $where_sql = implode(' AND ', $where);
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']) ?: 'start_date DESC';
        $offset = ($args['page'] - 1) * $args['per_page'];

        $count_sql = "SELECT COUNT(*) FROM $table WHERE $where_sql";
        $total = !empty($values) ? $wpdb->get_var($wpdb->prepare($count_sql, ...$values)) : $wpdb->get_var($count_sql);

        $sql = "SELECT * FROM $table WHERE $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        $all_values = array_merge($values, [$args['per_page'], $offset]);
        $items = $wpdb->get_results($wpdb->prepare($sql, ...$all_values));

        return [
            'items'       => $items,
            'total'       => (int) $total,
            'total_pages' => ceil($total / $args['per_page']),
            'page'        => (int) $args['page'],
        ];
    }

    public static function get_tour($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::table('tours') . " WHERE id = %d", $id
        ));
    }

    public static function get_tour_by_slug($slug) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::table('tours') . " WHERE slug = %s", $slug
        ));
    }

    public static function insert_tour($data) {
        global $wpdb;
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        $wpdb->insert(self::table('tours'), $data);
        return $wpdb->insert_id;
    }

    public static function update_tour($id, $data) {
        global $wpdb;
        $data['updated_at'] = current_time('mysql');
        return $wpdb->update(self::table('tours'), $data, ['id' => $id]);
    }

    public static function delete_tour($id) {
        global $wpdb;
        return $wpdb->delete(self::table('tours'), ['id' => $id]);
    }

    public static function get_tour_booked_count($tour_id) {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(participants), 0) FROM " . self::table('bookings') .
            " WHERE tour_id = %d AND status NOT IN ('cancelled')", $tour_id
        ));
    }

    // ─── BOOKINGS ────────────────────────────────────────

    public static function get_bookings($args = []) {
        global $wpdb;
        $table = self::table('bookings');
        $tours_table = self::table('tours');
        $defaults = [
            'status'   => '',
            'tour_id'  => 0,
            'user_id'  => 0,
            'search'   => '',
            'orderby'  => 'created_at',
            'order'    => 'DESC',
            'per_page' => 10,
            'page'     => 1,
        ];
        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $values = [];

        if ($args['status']) {
            $where[] = "b.status = %s";
            $values[] = $args['status'];
        }
        if ($args['tour_id']) {
            $where[] = "b.tour_id = %d";
            $values[] = $args['tour_id'];
        }
        if ($args['user_id']) {
            $where[] = "b.user_id = %d";
            $values[] = $args['user_id'];
        }
        if ($args['search']) {
            $where[] = "(b.booking_code LIKE %s)";
            $values[] = '%' . $wpdb->esc_like($args['search']) . '%';
        }

        $where_sql = implode(' AND ', $where);
        $orderby = sanitize_sql_orderby('b.' . $args['orderby'] . ' ' . $args['order']) ?: 'b.created_at DESC';
        $offset = ($args['page'] - 1) * $args['per_page'];

        $count_sql = "SELECT COUNT(*) FROM $table b WHERE $where_sql";
        $total = !empty($values) ? $wpdb->get_var($wpdb->prepare($count_sql, ...$values)) : $wpdb->get_var($count_sql);

        $sql = "SELECT b.*, t.title as tour_title, t.destination, t.start_date, t.end_date
                FROM $table b
                LEFT JOIN $tours_table t ON b.tour_id = t.id
                WHERE $where_sql ORDER BY $orderby LIMIT %d OFFSET %d";
        $all_values = array_merge($values, [$args['per_page'], $offset]);
        $items = $wpdb->get_results($wpdb->prepare($sql, ...$all_values));

        return [
            'items'       => $items,
            'total'       => (int) $total,
            'total_pages' => ceil($total / $args['per_page']),
            'page'        => (int) $args['page'],
        ];
    }

    public static function get_booking($id) {
        global $wpdb;
        $table = self::table('bookings');
        $tours_table = self::table('tours');
        return $wpdb->get_row($wpdb->prepare(
            "SELECT b.*, t.title as tour_title, t.destination, t.start_date, t.end_date, t.price as tour_price
             FROM $table b LEFT JOIN $tours_table t ON b.tour_id = t.id WHERE b.id = %d", $id
        ));
    }

    public static function insert_booking($data) {
        global $wpdb;
        $data['booking_code'] = self::generate_booking_code();
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');
        $wpdb->insert(self::table('bookings'), $data);
        return $wpdb->insert_id;
    }

    public static function update_booking($id, $data) {
        global $wpdb;
        $data['updated_at'] = current_time('mysql');
        return $wpdb->update(self::table('bookings'), $data, ['id' => $id]);
    }

    public static function delete_booking($id) {
        global $wpdb;
        return $wpdb->delete(self::table('bookings'), ['id' => $id]);
    }

    private static function generate_booking_code() {
        $date = current_time('Ymd');
        $rand = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        return "TS-{$date}-{$rand}";
    }

    // ─── MEMBERS ─────────────────────────────────────────

    public static function get_members($args = []) {
        global $wpdb;
        $table = self::table('members');
        $defaults = [
            'membership_level' => '',
            'search'           => '',
            'orderby'          => 'joined_at',
            'order'            => 'DESC',
            'per_page'         => 10,
            'page'             => 1,
        ];
        $args = wp_parse_args($args, $defaults);
        $where = ['1=1'];
        $values = [];

        if ($args['membership_level']) {
            $where[] = "m.membership_level = %s";
            $values[] = $args['membership_level'];
        }
        if ($args['search']) {
            $where[] = "(u.display_name LIKE %s OR u.user_email LIKE %s OR m.phone LIKE %s)";
            $s = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $s;
            $values[] = $s;
            $values[] = $s;
        }

        $where_sql = implode(' AND ', $where);
        $offset = ($args['page'] - 1) * $args['per_page'];

        $count_sql = "SELECT COUNT(*) FROM $table m
                      LEFT JOIN {$wpdb->users} u ON m.user_id = u.ID
                      WHERE $where_sql";
        $total = !empty($values) ? $wpdb->get_var($wpdb->prepare($count_sql, ...$values)) : $wpdb->get_var($count_sql);

        $sql = "SELECT m.*, u.display_name, u.user_email
                FROM $table m
                LEFT JOIN {$wpdb->users} u ON m.user_id = u.ID
                WHERE $where_sql ORDER BY m.joined_at DESC LIMIT %d OFFSET %d";
        $all_values = array_merge($values, [$args['per_page'], $offset]);
        $items = $wpdb->get_results($wpdb->prepare($sql, ...$all_values));

        return [
            'items'       => $items,
            'total'       => (int) $total,
            'total_pages' => ceil($total / $args['per_page']),
            'page'        => (int) $args['page'],
        ];
    }

    public static function get_member($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT m.*, u.display_name, u.user_email
             FROM " . self::table('members') . " m
             LEFT JOIN {$wpdb->users} u ON m.user_id = u.ID
             WHERE m.id = %d", $id
        ));
    }

    public static function get_member_by_user_id($user_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::table('members') . " WHERE user_id = %d", $user_id
        ));
    }

    public static function insert_member($data) {
        global $wpdb;
        $data['joined_at'] = current_time('mysql');
        $wpdb->insert(self::table('members'), $data);
        return $wpdb->insert_id;
    }

    public static function update_member($id, $data) {
        global $wpdb;
        return $wpdb->update(self::table('members'), $data, ['id' => $id]);
    }

    public static function update_member_by_user_id($user_id, $data) {
        global $wpdb;
        return $wpdb->update(self::table('members'), $data, ['user_id' => $user_id]);
    }

    // ─── REVIEWS ─────────────────────────────────────────

    public static function get_reviews_by_tour($tour_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, u.display_name
             FROM " . self::table('reviews') . " r
             LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
             WHERE r.tour_id = %d ORDER BY r.created_at DESC", $tour_id
        ));
    }

    public static function insert_review($data) {
        global $wpdb;
        $data['created_at'] = current_time('mysql');
        $wpdb->insert(self::table('reviews'), $data);
        return $wpdb->insert_id;
    }

    public static function get_tour_avg_rating($tour_id) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(rating) FROM " . self::table('reviews') . " WHERE tour_id = %d", $tour_id
        ));
    }

    // ─── STATS ───────────────────────────────────────────

    public static function get_dashboard_stats() {
        global $wpdb;
        $tours_table = self::table('tours');
        $bookings_table = self::table('bookings');
        $members_table = self::table('members');

        $active_tours = $wpdb->get_var("SELECT COUNT(*) FROM $tours_table WHERE status = 'published'");
        $total_bookings = $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $month_revenue = $wpdb->get_var("SELECT COALESCE(SUM(total_price), 0) FROM $bookings_table WHERE status IN ('paid','completed') AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $total_members = $wpdb->get_var("SELECT COUNT(*) FROM $members_table");

        return [
            'active_tours'   => (int) $active_tours,
            'total_bookings' => (int) $total_bookings,
            'month_revenue'  => (float) $month_revenue,
            'total_members'  => (int) $total_members,
        ];
    }

    public static function get_recent_bookings($limit = 10) {
        global $wpdb;
        $table = self::table('bookings');
        $tours_table = self::table('tours');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT b.*, t.title as tour_title, u.display_name
             FROM $table b
             LEFT JOIN $tours_table t ON b.tour_id = t.id
             LEFT JOIN {$wpdb->users} u ON b.user_id = u.ID
             ORDER BY b.created_at DESC LIMIT %d", $limit
        ));
    }

    public static function get_upcoming_tours($limit = 5) {
        global $wpdb;
        $table = self::table('tours');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE status = 'published' AND start_date >= CURDATE()
             ORDER BY start_date ASC LIMIT %d", $limit
        ));
    }

    public static function get_monthly_booking_stats($months = 6) {
        global $wpdb;
        $table = self::table('bookings');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT DATE_FORMAT(created_at, '%%Y-%%m') as month, COUNT(*) as total,
                    COALESCE(SUM(total_price), 0) as revenue
             FROM $table
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %d MONTH)
             GROUP BY month ORDER BY month ASC", $months
        ));
    }
}
