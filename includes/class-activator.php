<?php
namespace TravelShip;

if (!defined('ABSPATH')) exit;

class Activator {

    public static function activate() {
        self::create_tables();
        self::create_roles();
        self::create_pages();
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix . 'travelship_';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Tours table
        $sql_tours = "CREATE TABLE {$prefix}tours (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description LONGTEXT,
            destination VARCHAR(255),
            thumbnail_id BIGINT(20) UNSIGNED DEFAULT 0,
            gallery_ids TEXT,
            price DECIMAL(15,2) NOT NULL DEFAULT 0,
            max_participants INT(11) NOT NULL DEFAULT 0,
            start_date DATE,
            end_date DATE,
            itinerary LONGTEXT,
            includes TEXT,
            excludes TEXT,
            terms TEXT,
            status VARCHAR(20) NOT NULL DEFAULT 'draft',
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            created_by BIGINT(20) UNSIGNED DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY status (status),
            KEY start_date (start_date)
        ) $charset_collate;";

        dbDelta($sql_tours);

        // Bookings table
        $sql_bookings = "CREATE TABLE {$prefix}bookings (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_code VARCHAR(20) NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            tour_id BIGINT(20) UNSIGNED NOT NULL,
            participants INT(11) NOT NULL DEFAULT 1,
            total_price DECIMAL(15,2) NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            notes TEXT,
            payment_method VARCHAR(50),
            payment_proof BIGINT(20) UNSIGNED DEFAULT 0,
            paid_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY booking_code (booking_code),
            KEY user_id (user_id),
            KEY tour_id (tour_id),
            KEY status (status)
        ) $charset_collate;";

        dbDelta($sql_bookings);

        // Members table
        $sql_members = "CREATE TABLE {$prefix}members (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            emergency_contact VARCHAR(255),
            emergency_phone VARCHAR(20),
            id_number VARCHAR(30),
            membership_level VARCHAR(20) NOT NULL DEFAULT 'basic',
            points INT(11) NOT NULL DEFAULT 0,
            joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id),
            KEY membership_level (membership_level)
        ) $charset_collate;";

        dbDelta($sql_members);

        // Reviews table
        $sql_reviews = "CREATE TABLE {$prefix}reviews (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            tour_id BIGINT(20) UNSIGNED NOT NULL,
            rating TINYINT(1) NOT NULL DEFAULT 5,
            review TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY tour_id (tour_id)
        ) $charset_collate;";

        dbDelta($sql_reviews);

        update_option('travelship_db_version', TRAVELSHIP_VERSION);
    }

    private static function create_roles() {
        add_role('travelship_member', 'Travel Member', [
            'read' => true,
            'travelship_book_tour' => true,
            'travelship_view_dashboard' => true,
        ]);

        // Add capabilities to admin
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('travelship_manage_tours');
            $admin->add_cap('travelship_manage_bookings');
            $admin->add_cap('travelship_manage_members');
            $admin->add_cap('travelship_manage_settings');
        }
    }

    private static function create_pages() {
        // Create "My Dashboard" page with shortcode
        $dashboard_page = get_option('travelship_dashboard_page_id');
        if (!$dashboard_page || !get_post($dashboard_page)) {
            $page_id = wp_insert_post([
                'post_title'   => 'Travel Dashboard',
                'post_content' => '[travelship_dashboard]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => 1,
            ]);
            update_option('travelship_dashboard_page_id', $page_id);
        }

        // Create "Travel List" page with shortcode
        $list_page = get_option('travelship_tour_list_page_id');
        if (!$list_page || !get_post($list_page)) {
            $page_id = wp_insert_post([
                'post_title'   => 'Daftar Travel',
                'post_content' => '[travelship_tours]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => 1,
            ]);
            update_option('travelship_tour_list_page_id', $page_id);
        }
    }
}
