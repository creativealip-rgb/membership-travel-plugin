<?php
/**
 * Travel Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$tracker = new TMP_User_Travel_Tracker();
$membership = new TMP_Membership_Checker();
$user_id = get_current_user_id();
$stats = $tracker->get_stats($user_id);
$travels = $tracker->get_travels($user_id);
$can_add = $membership->can_add_destination($user_id);
?>

<div class="tmp-dashboard">
    <div class="tmp-dashboard-header">
        <h2>🗺️ <?php esc_html_e('My Travel Dashboard', 'travel-membership-pro'); ?></h2>
        
        <?php if (!is_wp_error($can_add)): ?>
            <button class="tmp-add-travel-btn" onclick="document.getElementById('tmp-add-travel-form').scrollIntoView({behavior: 'smooth'})">
                + <?php esc_html_e('Add New Travel', 'travel-membership-pro'); ?>
            </button>
        <?php else: ?>
            <a href="<?php echo esc_url($membership->get_upgrade_url()); ?>" class="tmp-add-travel-btn" style="background: #ffc107; color: #000;">
                <?php esc_html_e('Upgrade Membership', 'travel-membership-pro'); ?>
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Stats -->
    <div class="tmp-stats-grid">
        <div class="tmp-stat-card tmp-stat-destinations">
            <div class="tmp-stat-label"><?php esc_html_e('Destinations', 'travel-membership-pro'); ?></div>
            <div class="tmp-stat-number"><?php echo esc_html($stats['total_destinations']); ?></div>
        </div>
        
        <div class="tmp-stat-card tmp-stat-countries">
            <div class="tmp-stat-label"><?php esc_html_e('Countries', 'travel-membership-pro'); ?></div>
            <div class="tmp-stat-number"><?php echo esc_html($stats['countries']); ?></div>
        </div>
        
        <div class="tmp-stat-card tmp-stat-photos">
            <div class="tmp-stat-label"><?php esc_html_e('Photos', 'travel-membership-pro'); ?></div>
            <div class="tmp-stat-number"><?php echo esc_html($stats['photos']); ?></div>
        </div>
        
        <div class="tmp-stat-card">
            <div class="tmp-stat-label"><?php esc_html_e('Member Since', 'travel-membership-pro'); ?></div>
            <div class="tmp-stat-number" style="font-size: 1.5em;">
                <?php echo esc_html(date_i18n(get_option('date_format'), get_the_date('U', get_userdata($user_id)->user_registered))); ?>
            </div>
        </div>
    </div>
    
    <!-- Add Travel Form -->
    <?php if (!is_wp_error($can_add)): ?>
    <div class="tmp-travel-form" id="tmp-add-travel-form">
        <h3><?php esc_html_e('Add New Destination', 'travel-membership-pro'); ?></h3>
        
        <form id="tmp-add-travel-form" enctype="multipart/form-data">
            <div class="tmp-form-row">
                <div class="tmp-form-group">
                    <label for="tmp-destination-title">
                        <?php esc_html_e('Destination Name *', 'travel-membership-pro'); ?>
                    </label>
                    <input type="text" id="tmp-destination-title" name="title" required>
                </div>
                
                <div class="tmp-form-group">
                    <label for="tmp-visit-date">
                        <?php esc_html_e('Visit Date *', 'travel-membership-pro'); ?>
                    </label>
                    <input type="date" id="tmp-visit-date" name="visit_date" required>
                </div>
            </div>
            
            <div class="tmp-form-group">
                <label for="tmp-destination-description">
                    <?php esc_html_e('Description', 'travel-membership-pro'); ?>
                </label>
                <textarea id="tmp-destination-description" name="description" rows="4"></textarea>
            </div>
            
            <div class="tmp-form-row">
                <div class="tmp-form-group">
                    <label for="tmp-country">
                        <?php esc_html_e('Country', 'travel-membership-pro'); ?>
                    </label>
                    <select id="tmp-country" name="country_id">
                        <option value=""><?php esc_html_e('Select Country', 'travel-membership-pro'); ?></option>
                        <?php
                        $countries = get_terms(['taxonomy' => 'country', 'hide_empty' => false]);
                        if ($countries && !is_wp_error($countries)):
                            foreach ($countries as $country):
                        ?>
                            <option value="<?php echo esc_attr($country->term_id); ?>">
                                <?php echo esc_html($country->name); ?>
                            </option>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>
                
                <div class="tmp-form-group">
                    <label for="tmp-category">
                        <?php esc_html_e('Category', 'travel-membership-pro'); ?>
                    </label>
                    <select id="tmp-category" name="category_ids">
                        <option value=""><?php esc_html_e('Select Category', 'travel-membership-pro'); ?></option>
                        <?php
                        $categories = get_terms(['taxonomy' => 'travel_category', 'hide_empty' => false]);
                        if ($categories && !is_wp_error($categories)):
                            foreach ($categories as $category):
                        ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>">
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="tmp-form-row">
                <div class="tmp-form-group">
                    <label for="tmp-location">
                        <?php esc_html_e('Location', 'travel-membership-pro'); ?>
                    </label>
                    <input type="text" id="tmp-location" name="location" placeholder="City, Region">
                </div>
                
                <div class="tmp-form-group">
                    <label for="tmp-rating">
                        <?php esc_html_e('Rating', 'travel-membership-pro'); ?>
                    </label>
                    <select id="tmp-rating" name="rating">
                        <option value="0"><?php esc_html_e('Select Rating', 'travel-membership-pro'); ?></option>
                        <option value="1">⭐</option>
                        <option value="2">⭐⭐</option>
                        <option value="3">⭐⭐⭐</option>
                        <option value="4">⭐⭐⭐⭐</option>
                        <option value="5">⭐⭐⭐⭐⭐</option>
                    </select>
                </div>
            </div>
            
            <div class="tmp-form-group">
                <label><?php esc_html_e('Photos', 'travel-membership-pro'); ?></label>
                <div class="tmp-photo-upload">
                    <p>📁 <?php esc_html_e('Click to upload or drag and drop', 'travel-membership-pro'); ?></p>
                    <p><small><?php esc_html_e('JPG, PNG, GIF, WebP (max 5MB)', 'travel-membership-pro'); ?></small></p>
                    <input type="file" accept="image/*" multiple style="display: none;">
                </div>
                <div class="tmp-photo-preview"></div>
            </div>
            
            <div class="tmp-form-group">
                <label for="tmp-notes">
                    <?php esc_html_e('Personal Notes', 'travel-membership-pro'); ?>
                </label>
                <textarea id="tmp-notes" name="notes" rows="3" placeholder="Your memories, tips, or highlights..."></textarea>
            </div>
            
            <!-- Hidden fields for coordinates (can be populated via map picker) -->
            <input type="hidden" id="tmp-lat" name="coordinates[lat]" value="">
            <input type="hidden" id="tmp-lng" name="coordinates[lng]" value="">
            
            <button type="submit" class="tmp-btn tmp-btn-primary">
                <?php esc_html_e('Add Travel', 'travel-membership-pro'); ?>
            </button>
        </form>
    </div>
    <?php else: ?>
    <div class="tmp-upgrade-notice">
        <p>
            <strong><?php echo esc_html($can_add->get_error_message()); ?></strong>
            <a href="<?php echo esc_url($membership->get_upgrade_url()); ?>" class="button button-primary">
                <?php esc_html_e('Upgrade Now', 'travel-membership-pro'); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>
    
    <!-- Travel Map -->
    <div style="margin: 30px 0;">
        <h3><?php esc_html_e('My Travel Map', 'travel-membership-pro'); ?></h3>
        <?php echo do_shortcode('[travel_map user_id="' . $user_id . '" height="400"]'); ?>
    </div>
    
    <!-- Travel History -->
    <div>
        <h3><?php esc_html_e('My Travel History', 'travel-membership-pro'); ?></h3>
        <?php echo do_shortcode('[user_travel_history limit="12"]'); ?>
    </div>
</div>
