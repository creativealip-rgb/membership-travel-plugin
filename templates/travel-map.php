<?php
/**
 * Travel Map Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = absint($atts['user_id']);
$height = esc_attr($atts['height']);
$width = esc_attr($atts['width']);
$zoom = esc_attr($atts['zoom']);
?>

<div class="tmp-map-container" 
     data-lat="-0.7893" 
     data-lng="113.9213" 
     data-zoom="<?php echo esc_attr($zoom); ?>">
    <div id="tmp-travel-map" style="height: <?php echo esc_attr($height); ?>px; width: <?php echo esc_attr($width); ?>;"></div>
</div>
