<?php
/**
 * Reports View
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get statistics
$total_users = count_users()['total_users'];
$users_with_travels = count(get_users(['meta_key' => '_visited_destinations', 'fields' => 'ID']));

// Get top destinations
$top_destinations = new WP_Query([
    'post_type' => 'destination',
    'posts_per_page' => 10,
    'orderby' => 'meta_value_num',
    'meta_key' => '_visit_count',
]);

// Get top countries
$countries = get_terms([
    'taxonomy' => 'country',
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 10,
]);
?>

<div class="wrap">
    <h1><?php echo esc_html__('Travel Reports', 'travel-membership-pro'); ?></h1>
    
    <div style="display: flex; gap: 20px; margin: 20px 0;">
        <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
            <h3><?php esc_html_e('Total Users', 'travel-membership-pro'); ?></h3>
            <p style="font-size: 2em; margin: 10px 0;"><?php echo number_format($total_users); ?></p>
        </div>
        
        <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
            <h3><?php esc_html_e('Active Travelers', 'travel-membership-pro'); ?></h3>
            <p style="font-size: 2em; margin: 10px 0;"><?php echo number_format($users_with_travels); ?></p>
        </div>
        
        <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
            <h3><?php esc_html_e('Total Destinations', 'travel-membership-pro'); ?></h3>
            <p style="font-size: 2em; margin: 10px 0;"><?php echo number_format(wp_count_posts('destination')->publish); ?></p>
        </div>
        
        <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
            <h3><?php esc_html_e('Countries Visited', 'travel-membership-pro'); ?></h3>
            <p style="font-size: 2em; margin: 10px 0;"><?php echo number_format(count(get_terms(['taxonomy' => 'country', 'hide_empty' => false]))); ?></p>
        </div>
    </div>
    
    <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
            <h2><?php esc_html_e('Top Countries', 'travel-membership-pro'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Country', 'travel-membership-pro'); ?></th>
                        <th><?php esc_html_e('Visits', 'travel-membership-pro'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($countries as $country): ?>
                        <tr>
                            <td><?php echo esc_html($country->name); ?></td>
                            <td><?php echo esc_html($country->count); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
        <h2><?php esc_html_e('Export Data', 'travel-membership-pro'); ?></h2>
        <p><?php esc_html_e('Download all travel data in CSV format', 'travel-membership-pro'); ?></p>
        <button class="button button-primary" onclick="tmpExportData()">
            <?php esc_html_e('Export to CSV', 'travel-membership-pro'); ?>
        </button>
    </div>
</div>

<script>
function tmpExportData() {
    // AJAX call to export endpoint
    jQuery.post(ajaxurl, {
        action: 'tmp_export_data',
        nonce: '<?php echo wp_create_nonce('tmp_export_nonce'); ?>'
    }, function(response) {
        if (response.success) {
            window.location.href = response.data.download_url;
        }
    });
}
</script>
