<?php
/**
 * User Travel History Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$tracker = new TMP_User_Travel_Tracker();
?>

<?php if (empty($travels)): ?>
    <div class="tmp-no-travels" style="text-align: center; padding: 40px; color: #666;">
        <p style="font-size: 3em; margin: 0;">🗺️</p>
        <p><?php esc_html_e('No travels yet. Start exploring!', 'travel-membership-pro'); ?></p>
    </div>
<?php else: ?>
    <div class="tmp-travel-history">
        <?php foreach ($travels as $travel): 
            $post_id = $travel['destination_id'] ?? 0;
            if (!$post_id) continue;
            
            $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
            $title = get_the_title($post_id);
            $excerpt = get_the_excerpt($post_id);
            $permalink = get_permalink($post_id);
            
            // Get country
            $countries = get_the_terms($post_id, 'country');
            $country_name = $countries && !is_wp_error($countries) ? $countries[0]->name : '';
        ?>
            <div class="tmp-travel-card">
                <?php if ($thumbnail): ?>
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>" class="tmp-travel-thumbnail">
                <?php else: ?>
                    <div class="tmp-travel-thumbnail" style="background: #eee; display: flex; align-items: center; justify-content: center; font-size: 3em;">
                        🌍
                    </div>
                <?php endif; ?>
                
                <div class="tmp-travel-content">
                    <h4 class="tmp-travel-title">
                        <?php echo esc_html($title); ?>
                    </h4>
                    
                    <div class="tmp-travel-meta">
                        <?php if ($country_name): ?>
                            <span>📍 <?php echo esc_html($country_name); ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($travel['visit_date'])): ?>
                            <span>📅 <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($travel['visit_date']))); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($excerpt): ?>
                        <p class="tmp-travel-excerpt">
                            <?php echo esc_html(wp_trim_words($excerpt, 20)); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($travel['rating'])): ?>
                        <div class="tmp-rating" style="margin-bottom: 10px;">
                            <?php echo str_repeat('⭐', $travel['rating']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="tmp-travel-actions">
                        <a href="<?php echo esc_url($permalink); ?>" class="tmp-btn tmp-btn-primary">
                            <?php esc_html_e('View', 'travel-membership-pro'); ?>
                        </a>
                        
                        <?php if (get_current_user_id() === $user_id): ?>
                            <button class="tmp-btn tmp-btn-danger tmp-remove-travel" 
                                    data-destination-id="<?php echo esc_attr($post_id); ?>">
                                <?php esc_html_e('Remove', 'travel-membership-pro'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
