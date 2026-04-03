<?php
/**
 * Admin Dashboard Widgets
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Dashboard_Widgets {
    
    public function __construct() {
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets']);
    }
    
    /**
     * Add dashboard widgets
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'tmp_bookings_widget',
            '🎫 Travel Bookings Overview',
            [$this, 'render_bookings_widget']
        );
        
        wp_add_dashboard_widget(
            'tmp_stats_widget',
            '📊 Travel Statistics',
            [$this, 'render_stats_widget']
        );
    }
    
    /**
     * Render bookings widget
     */
    public function render_bookings_widget() {
        // Get recent bookings
        $recent_bookings = get_posts([
            'post_type' => 'tour_booking',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        
        // Get booking counts by status
        $all_bookings = get_posts([
            'post_type' => 'tour_booking',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);
        
        $status_counts = [
            'pending_payment' => 0,
            'payment_uploaded' => 0,
            'paid' => 0,
            'confirmed' => 0,
            'cancelled' => 0,
            'completed' => 0,
        ];
        
        foreach ($all_bookings as $booking_id) {
            $status = get_post_meta($booking_id, '_booking_status', true);
            if (isset($status_counts[$status])) {
                $status_counts[$status]++;
            }
        }
        
        ?>
        <div class="tmp-dashboard-widget">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px;">
                <div style="background: #fff3cd; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 2em; font-weight: bold; color: #856404;"><?php echo $status_counts['pending_payment']; ?></div>
                    <div style="color: #856404; font-size: 0.9em;">⏳ Pending</div>
                </div>
                <div style="background: #d4edda; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 2em; font-weight: bold; color: #155724;"><?php echo $status_counts['confirmed']; ?></div>
                    <div style="color: #155724; font-size: 0.9em;">✓ Confirmed</div>
                </div>
                <div style="background: #cce5ff; padding: 15px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 2em; font-weight: bold; color: #004085;"><?php echo count($all_bookings); ?></div>
                    <div style="color: #004085; font-size: 0.9em;">📦 Total</div>
                </div>
            </div>
            
            <h4 style="margin-bottom: 10px;">Recent Bookings:</h4>
            <?php if (empty($recent_bookings)): ?>
                <p style="color: #666;">No bookings yet.</p>
            <?php else: ?>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($recent_bookings as $booking): 
                        $booking_code = get_post_meta($booking->ID, '_booking_code', true);
                        $tour_id = get_post_meta($booking->ID, '_tour_id', true);
                        $tour = get_post($tour_id);
                        $status = get_post_meta($booking->ID, '_booking_status', true);
                        $total = get_post_meta($booking->ID, '_total_amount', true);
                        
                        $status_colors = [
                            'pending_payment' => '#fff3cd',
                            'payment_uploaded' => '#cce5ff',
                            'paid' => '#d4edda',
                            'confirmed' => '#d4edda',
                            'cancelled' => '#f8d7da',
                            'completed' => '#d4edda',
                        ];
                    ?>
                        <li style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong><?php echo esc_html($booking_code); ?></strong><br>
                                <small style="color: #666;"><?php echo $tour ? esc_html($tour->post_title) : 'Deleted'; ?></small>
                            </div>
                            <div style="text-align: right;">
                                <span style="background: <?php echo $status_colors[$status] ?? '#f0f0f1'; ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.8em;">
                                    <?php echo esc_html(str_replace('_', ' ', $status)); ?>
                                </span><br>
                                <small style="color: #0073aa; font-weight: bold;">Rp <?php echo number_format($total, 0, ',', '.'); ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <p style="margin-top: 15px; text-align: center;">
                <a href="<?php echo admin_url('edit.php?post_type=tour_booking'); ?>" class="button button-primary">View All Bookings</a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render statistics widget
     */
    public function render_stats_widget() {
        // Get tour counts
        $tours_count = wp_count_posts('tour');
        
        // Get user counts
        $user_count = count_users();
        
        // Get country counts
        $countries = get_terms(['taxonomy' => 'country', 'hide_empty' => false]);
        
        // Get revenue (confirmed bookings only)
        $confirmed_bookings = get_posts([
            'post_type' => 'tour_booking',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_booking_status',
                    'value' => ['confirmed', 'completed', 'paid'],
                    'compare' => 'IN',
                ],
            ],
        ]);
        
        $revenue = 0;
        foreach ($confirmed_bookings as $booking) {
            $revenue += absint(get_post_meta($booking->ID, '_total_amount', true));
        }
        
        ?>
        <div class="tmp-dashboard-widget">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px; color: #fff; text-align: center;">
                    <div style="font-size: 2.5em; font-weight: bold;"><?php echo $tours_count->publish; ?></div>
                    <div style="opacity: 0.9;">🎫 Active Tours</div>
                </div>
                
                <div style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); padding: 20px; border-radius: 8px; color: #fff; text-align: center;">
                    <div style="font-size: 2.5em; font-weight: bold;"><?php echo $user_count['total_users']; ?></div>
                    <div style="opacity: 0.9;">👥 Total Users</div>
                </div>
                
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 8px; color: #fff; text-align: center;">
                    <div style="font-size: 2.5em; font-weight: bold;"><?php echo count($countries); ?></div>
                    <div style="opacity: 0.9;">🌍 Countries</div>
                </div>
                
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 20px; border-radius: 8px; color: #fff; text-align: center;">
                    <div style="font-size: 2em; font-weight: bold;">Rp <?php echo number_format($revenue/1000000, 1); ?>M</div>
                    <div style="opacity: 0.9;">💰 Revenue</div>
                </div>
            </div>
            
            <p style="margin-top: 20px; text-align: center;">
                <a href="<?php echo admin_url('edit.php?post_type=tour&page=tmp-reports'); ?>" class="button">View Detailed Reports</a>
            </p>
        </div>
        <?php
    }
}

new TMP_Dashboard_Widgets();
