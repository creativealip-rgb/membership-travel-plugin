<?php
/**
 * Tour List Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="tmpb-tour-list">
    <?php if ($tours->have_posts()): ?>
        <div class="tmpb-tours-grid">
            <?php while ($tours->have_posts()): $tours->the_post(); 
                $tour_id = get_the_ID();
                $price = get_post_meta($tour_id, '_tour_price', true);
                $duration = get_post_meta($tour_id, '_tour_duration_days', true);
                $location = get_post_meta($tour_id, '_tour_location', true);
                $thumbnail = get_the_post_thumbnail_url($tour_id, 'medium');
            ?>
                <div class="tmpb-tour-card">
                    <?php if ($thumbnail): ?>
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title(); ?>" class="tmpb-tour-image">
                    <?php else: ?>
                        <div class="tmpb-tour-image-placeholder">🗺️</div>
                    <?php endif; ?>
                    
                    <div class="tmpb-tour-content">
                        <h3 class="tmpb-tour-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        
                        <?php if ($location): ?>
                            <p class="tmpb-tour-location">📍 <?php echo esc_html($location); ?></p>
                        <?php endif; ?>
                        
                        <div class="tmpb-tour-meta">
                            <span>⏱️ <?php echo esc_html($duration); ?> <?php esc_html_e('days', 'travel-membership-pro'); ?></span>
                        </div>
                        
                        <div class="tmpb-tour-footer">
                            <div class="tmpb-tour-price">
                                <span class="tmpb-price-label"><?php esc_html_e('From', 'travel-membership-pro'); ?></span>
                                <span class="tmpb-price-amount">Rp <?php echo number_format(absint($price), 0, ',', '.'); ?></span>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="tmpb-btn tmpb-btn-primary">
                                <?php esc_html_e('View Details', 'travel-membership-pro'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php wp_reset_postdata(); ?>
    <?php else: ?>
        <div class="tmpb-no-tours">
            <p><?php esc_html_e('No tours available at the moment.', 'travel-membership-pro'); ?></p>
        </div>
    <?php endif; ?>
</div>
