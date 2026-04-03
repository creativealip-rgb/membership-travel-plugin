<?php
/**
 * Tour Single Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$tour_id = get_the_ID();
$price = get_post_meta($tour_id, '_tour_price', true);
$duration = get_post_meta($tour_id, '_tour_duration_days', true);
$quota = get_post_meta($tour_id, '_tour_quota', true);
$min_pax = get_post_meta($tour_id, '_tour_min_pax', true);
$location = get_post_meta($tour_id, '_tour_location', true);
$includes = get_post_meta($tour_id, '_tour_includes', true);
$excludes = get_post_meta($tour_id, '_tour_excludes', true);
$itinerary = get_post_meta($tour_id, '_tour_itinerary', true);
$gallery = get_post_meta($tour_id, '_tour_gallery', true);
?>

<div class="tmpb-tour-single">
    <div class="tmpb-tour-header">
        <h1><?php the_title(); ?></h1>
        
        <?php if ($location): ?>
            <p class="tmpb-tour-location">📍 <?php echo esc_html($location); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="tmpb-tour-content-grid">
        <div class="tmpb-tour-main">
            <!-- Gallery -->
            <?php if ($gallery): 
                $gallery_ids = explode(',', $gallery);
            ?>
                <div class="tmpb-tour-gallery">
                    <?php foreach (array_slice($gallery_ids, 0, 5) as $img_id): 
                        $img = wp_get_attachment_image($img_id, 'large');
                        if ($img): echo $img; endif;
                    endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Description -->
            <div class="tmpb-tour-description">
                <h2><?php esc_html_e('Tour Overview', 'travel-membership-pro'); ?></h2>
                <?php the_content(); ?>
            </div>
            
            <!-- Itinerary -->
            <?php if ($itinerary && is_array($itinerary)): ?>
                <div class="tmpb-tour-itinerary">
                    <h2><?php esc_html_e('Itinerary', 'travel-membership-pro'); ?></h2>
                    <?php foreach ($itinerary as $day): ?>
                        <div class="tmpb-itinerary-day">
                            <h3><?php echo esc_html($day['day']); ?></h3>
                            <p><?php echo esc_html($day['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Includes/Excludes -->
            <div class="tmpb-tour-includes-excludes">
                <div class="tmpb-tour-includes">
                    <h3>✅ <?php esc_html_e('Included', 'travel-membership-pro'); ?></h3>
                    <ul>
                        <?php foreach (explode("\n", $includes) as $item): 
                            if (trim($item)):
                        ?>
                            <li><?php echo esc_html(trim($item)); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
                
                <div class="tmpb-tour-excludes">
                    <h3>❌ <?php esc_html_e('Excluded', 'travel-membership-pro'); ?></h3>
                    <ul>
                        <?php foreach (explode("\n", $excludes) as $item): 
                            if (trim($item)):
                        ?>
                            <li><?php echo esc_html(trim($item)); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="tmpb-tour-sidebar">
            <div class="tmpb-booking-card">
                <div class="tmpb-booking-price">
                    <span class="tmpb-price-amount">Rp <?php echo number_format(absint($price), 0, ',', '.'); ?></span>
                    <span class="tmpb-price-label"><?php esc_html_e('per person', 'travel-membership-pro'); ?></span>
                </div>
                
                <div class="tmpb-booking-meta">
                    <div>⏱️ <?php echo esc_html($duration); ?> <?php esc_html_e('days', 'travel-membership-pro'); ?></div>
                    <div>👥 <?php esc_html_e('Min', 'travel-membership-pro'); ?>: <?php echo esc_html($min_pax); ?> <?php esc_html_e('pax', 'travel-membership-pro'); ?></div>
                    <div>🎫 <?php esc_html_e('Quota', 'travel-membership-pro'); ?>: <?php echo esc_html($quota); ?> <?php esc_html_e('persons', 'travel-membership-pro'); ?></div>
                </div>
                
                <?php echo do_shortcode('[booking_form tour_id="' . $tour_id . '"]'); ?>
            </div>
        </div>
    </div>
</div>
