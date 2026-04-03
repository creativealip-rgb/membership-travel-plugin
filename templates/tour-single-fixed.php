<?php
/**
 * Tour Single Template - FIXED v9
 */

if (!defined('ABSPATH')) {
    exit;
}

$tour_id = get_the_ID();
if (!$tour_id) {
    $tour_id = get_queried_object_id();
}

$price = get_post_meta($tour_id, 'price', true);
$duration = get_post_meta($tour_id, '_tour_duration_days', true);
$quota = get_post_meta($tour_id, '_tour_quota', true);
$min_pax = get_post_meta($tour_id, '_tour_min_pax', true);
$location = get_post_meta($tour_id, '_tour_location', true);
$includes = get_post_meta($tour_id, '_tour_includes', true);
$excludes = get_post_meta($tour_id, '_tour_excludes', true);
$itinerary = get_post_meta($tour_id, '_tour_itinerary', true);
$gallery = get_post_meta($tour_id, '_tour_gallery', true);

get_header();
?>



<div class="tmpb-wrapper">
<div class="tmpb-tour-single">
    <div class="tmpb-tour-header">
        <h1><?php echo esc_html(get_the_title($tour_id)); ?></h1>
        <?php if ($location): ?><p>📍 <?php echo esc_html($location); ?></p><?php endif; ?>
    </div>
    
    <div class="tmpb-tour-content-grid">
        <div class="tmpb-tour-main">
            <?php 
            if ($gallery): 
                $gallery_ids = is_array($gallery) ? $gallery : explode(',', $gallery);
                echo '<div class="tmpb-tour-gallery">';
                foreach (array_slice($gallery_ids, 0, 5) as $img_id): 
                    $img = wp_get_attachment_image($img_id, 'large');
                    if ($img): echo '<div>' . $img . '</div>'; endif;
                endforeach;
                echo '</div>';
            endif; 
            ?>
            
            <div class="tmpb-tour-description">
                <h2>Tour Overview</h2>
                <div><?php echo get_the_content(null, false, $tour_id); ?></div>
            </div>
            
            <?php if ($itinerary && is_array($itinerary)): ?>
                <div class="tmpb-tour-itinerary">
                    <h2>Itinerary</h2>
                    <?php foreach ($itinerary as $day): ?>
                        <div class="tmpb-itinerary-day">
                            <h3><?php echo esc_html($day['day']); ?></h3>
                            <p><?php echo esc_html($day['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="tmpb-tour-includes-excludes">
                <div class="tmpb-tour-includes">
                    <h3>✅ Included</h3>
                    <ul><?php foreach (explode("\n", $includes) as $item): if (trim($item)): ?>
                        <li><?php echo esc_html(trim($item)); ?></li>
                    <?php endif; endforeach; ?></ul>
                </div>
                <div class="tmpb-tour-excludes">
                    <h3>❌ Excluded</h3>
                    <ul><?php foreach (explode("\n", $excludes) as $item): if (trim($item)): ?>
                        <li><?php echo esc_html(trim($item)); ?></li>
                    <?php endif; endforeach; ?></ul>
                </div>
            </div>
        </div>
        
        <div class="tmpb-tour-sidebar">
            <div class="tmpb-booking-card">
                <div class="tmpb-booking-price">
                    <span class="tmpb-price-amount">Rp <?php echo number_format(absint($price), 0, ',', '.'); ?></span>
                    <span class="tmpb-price-label">per person</span>
                </div>
                <div class="tmpb-booking-meta">
                    <div>⏱️ <?php echo esc_html($duration); ?> days</div>
                    <div>👥 Min: <?php echo esc_html($min_pax); ?> pax</div>
                    <div>🎫 Quota: <?php echo esc_html($quota); ?> persons</div>
                </div>
                <?php echo do_shortcode('[booking_form tour_id="' . $tour_id . '"]'); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
</div>
