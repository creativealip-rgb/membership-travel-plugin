<?php
/**
 * Tour Single Template - With Inline CSS (Guaranteed to Work!)
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

get_header();
?>

<style>
.tmpb-tour-single{max-width:1200px;margin:0 auto;padding:20px}
.tmpb-tour-header{margin-bottom:30px;padding-bottom:20px;border-bottom:2px solid #eee}
.tmpb-tour-header h1{font-size:2em;margin-bottom:10px}
.tmpb-tour-content-grid{display:grid;grid-template-columns:2fr 1fr;gap:30px;margin-top:30px}
.tmpb-tour-gallery{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:30px}
.tmpb-tour-gallery img{width:100%;height:200px;object-fit:cover;border-radius:8px}
.tmpb-tour-description{margin-bottom:30px}
.tmpb-tour-description h2{font-size:1.5em;margin-bottom:15px}
.tmpb-itinerary-day{background:#f9f9f9;padding:15px;margin:10px 0;border-radius:8px;border-left:4px solid #0073aa}
.tmpb-itinerary-day h3{color:#0073aa;margin:0 0 10px}
.tmpb-tour-includes-excludes{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:30px}
.tmpb-tour-includes ul,.tmpb-tour-excludes ul{list-style:disc;padding-left:20px}
.tmpb-tour-includes li,.tmpb-tour-excludes li{padding:5px 0}
.tmpb-booking-card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);position:sticky;top:20px}
.tmpb-booking-price{text-align:center;margin-bottom:20px;padding-bottom:20px;border-bottom:2px solid #eee}
.tmpb-price-amount{display:block;font-size:2em;color:#0073aa;font-weight:bold}
.tmpb-price-label{color:#666;font-size:0.9em}
.tmpb-booking-meta{margin-bottom:20px;padding:15px;background:#f9f9f9;border-radius:8px}
.tmpb-booking-meta div{margin:8px 0}
@media(max-width:768px){.tmpb-tour-content-grid{grid-template-columns:1fr}.tmpb-tour-includes-excludes{grid-template-columns:1fr}}
</style>

<div class="tmpb-tour-single">
    <div class="tmpb-tour-header">
        <h1><?php the_title(); ?></h1>
        <?php if ($location): ?><p>📍 <?php echo esc_html($location); ?></p><?php endif; ?>
    </div>
    
    <div class="tmpb-tour-content-grid">
        <div class="tmpb-tour-main">
            <?php if ($gallery): 
                $gallery_ids = is_array($gallery) ? $gallery : explode(',', $gallery);
                foreach (array_slice($gallery_ids, 0, 5) as $img_id): 
                    $img = wp_get_attachment_image($img_id, 'large');
                    if ($img): echo '<div class="tmpb-tour-gallery"><div>' . $img . '</div></div>'; endif;
                endforeach;
            endif; ?>
            
            <div class="tmpb-tour-description">
                <h2>Tour Overview</h2>
                <div><?php the_content(); ?></div>
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

<?php get_footer();
