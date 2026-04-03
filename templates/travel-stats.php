<?php
/**
 * Travel Stats Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$tracker = new TMP_User_Travel_Tracker();
$stats = $tracker->get_stats($user_id);
?>

<div class="tmp-stats-grid">
    <div class="tmp-stat-card">
        <div class="tmp-stat-label"><?php esc_html_e('Destinations', 'travel-membership-pro'); ?></div>
        <div class="tmp-stat-number"><?php echo esc_html($stats['total_destinations']); ?></div>
    </div>
    
    <div class="tmp-stat-card">
        <div class="tmp-stat-label"><?php esc_html_e('Countries', 'travel-membership-pro'); ?></div>
        <div class="tmp-stat-number"><?php echo esc_html($stats['countries']); ?></div>
    </div>
    
    <div class="tmp-stat-card">
        <div class="tmp-stat-label"><?php esc_html_e('Photos', 'travel-membership-pro'); ?></div>
        <div class="tmp-stat-number"><?php echo esc_html($stats['photos']); ?></div>
    </div>
    
    <div class="tmp-stat-card">
        <div class="tmp-stat-label"><?php esc_html_e('First Travel', 'travel-membership-pro'); ?></div>
        <div class="tmp-stat-number" style="font-size: 1.2em;">
            <?php 
            if ($stats['first_travel']) {
                echo esc_html(date_i18n(get_option('date_format'), strtotime($stats['first_travel'])));
            } else {
                esc_html_e('N/A', 'travel-membership-pro');
            }
            ?>
        </div>
    </div>
</div>
