<?php
/**
 * Export Bookings to CSV
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Export_Bookings {
    
    public function __construct() {
        add_action('admin_post_tmp_export_bookings', [$this, 'export_bookings']);
        add_action('admin_post_tmp_export_bookings_csv', [$this, 'export_bookings_csv']);
    }
    
    /**
     * Export bookings to CSV
     */
    public function export_bookings_csv() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        // Get all bookings
        $bookings = get_posts([
            'post_type' => 'tour_booking',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="bookings-export-' . date('Y-m-d') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add column headers
        fputcsv($output, [
            'Booking Code',
            'Tour Name',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Pax',
            'Total Amount',
            'Travel Date',
            'Booking Date',
            'Status',
            'Payment Method',
        ]);
        
        // Add booking data
        foreach ($bookings as $booking) {
            $tour_id = get_post_meta($booking->ID, '_tour_id', true);
            $tour = get_post($tour_id);
            
            fputcsv($output, [
                get_post_meta($booking->ID, '_booking_code', true),
                $tour ? $tour->post_title : 'Deleted',
                get_post_meta($booking->ID, '_customer_name', true),
                get_post_meta($booking->ID, '_customer_email', true),
                get_post_meta($booking->ID, '_customer_phone', true),
                get_post_meta($booking->ID, '_pax', true),
                get_post_meta($booking->ID, '_total_amount', true),
                get_post_meta($booking->ID, '_travel_date', true),
                get_post_meta($booking->ID, '_booking_date', true),
                get_post_meta($booking->ID, '_booking_status', true),
                get_post_meta($booking->ID, '_payment_method', true),
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Add export button to admin page
     */
    public function add_export_button() {
        ?>
        <div style="margin: 20px 0;">
            <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=tmp_export_bookings_csv'), 'tmp_export_bookings'); ?>" 
               class="button button-primary" 
               style="padding: 10px 20px; font-size: 14px;">
                📊 Export Bookings to CSV
            </a>
        </div>
        <?php
    }
}
