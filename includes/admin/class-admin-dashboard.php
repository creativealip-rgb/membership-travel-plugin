<?php
namespace TravelShip\Admin;

if (!defined('ABSPATH')) exit;

use TravelShip\DB;
use TravelShip\Helpers\Utils;

class AdminDashboard {

    public function render() {
        $stats = DB::get_dashboard_stats();
        $recent_bookings = DB::get_recent_bookings(10);
        $upcoming_tours = DB::get_upcoming_tours(5);
        $monthly_stats = DB::get_monthly_booking_stats(6);

        include TRAVELSHIP_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }
}
