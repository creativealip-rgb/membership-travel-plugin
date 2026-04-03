<?php
/**
 * All Member Travels View - Enhanced
 * Shows all member travel history with detailed info
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get all destination posts (user-submitted travels)
$destinations = get_posts([
    'post_type' => 'destination',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
]);

// Group by user
$user_travels = [];
foreach ($destinations as $dest) {
    $user_id = get_post_meta($dest->ID, '_user_id', true);
    if ($user_id) {
        if (!isset($user_travels[$user_id])) {
            $user_travels[$user_id] = [];
        }
        $user_travels[$user_id][] = $dest;
    }
}

// Get users
$user_ids = array_keys($user_travels);
$users = !empty($user_ids) ? get_users(['include' => $user_ids]) : [];
?>

<div class="wrap">
    <h1 style="margin-bottom: 20px;">
        🌍 <?php echo esc_html__('All Member Travels', 'travel-membership-pro'); ?>
    </h1>
    
    <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
            <div style="text-align: center;">
                <div style="font-size: 2.5em; font-weight: bold; color: #2563eb;"><?php echo count($destinations); ?></div>
                <div style="color: #64748b;">Total Travels</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2.5em; font-weight: bold; color: #10b981;"><?php echo count($users); ?></div>
                <div style="color: #64748b;">Active Members</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2.5em; font-weight: bold; color: #f59e0b;"><?php echo count(array_unique(array_map(function($d) { return get_post_meta($d->ID, '_country', true); }, $destinations))); ?></div>
                <div style="color: #64748b;">Countries Visited</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2.5em; font-weight: bold; color: #8b5cf6;"><?php echo array_sum(array_map(function($uid) use ($user_travels) { return count($user_travels[$uid]); }, $user_ids)); ?></div>
                <div style="color: #64748b;">Destinations</div>
            </div>
        </div>
    </div>
    
    <h2 style="margin: 30px 0 15px 0;">
        📍 Member Travel History
    </h2>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 200px;"><?php esc_html_e('Member', 'travel-membership-pro'); ?></th>
                <th><?php esc_html_e('Destinations Visited', 'travel-membership-pro'); ?></th>
                <th style="width: 150px;"><?php esc_html_e('Countries', 'travel-membership-pro'); ?></th>
                <th style="width: 150px;"><?php esc_html_e('Total Travels', 'travel-membership-pro'); ?></th>
                <th style="width: 150px;"><?php esc_html_e('Last Travel', 'travel-membership-pro'); ?></th>
                <th style="width: 150px;"><?php esc_html_e('Actions', 'travel-membership-pro'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">
                        <div style="font-size: 48px; margin-bottom: 16px;">🌍</div>
                        <strong style="color: #64748b;">No member travels yet</strong><br>
                        <small style="color: #94a3b8;">Members can add their travel history from My Travels page</small>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): 
                    $travels = $user_travels[$user->ID] ?? [];
                    
                    // Get countries
                    $countries = array_unique(array_map(function($d) {
                        return get_post_meta($d->ID, '_country', true);
                    }, $travels));
                    $countries = array_filter($countries);
                    
                    // Get last travel date
                    $last_travel = !empty($travels) ? get_post_meta($travels[0]->ID, '_visit_date', true) : '';
                ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                    <?php echo strtoupper(substr($user->display_name, 0, 1)); ?>
                                </div>
                                <div>
                                    <strong><?php echo esc_html($user->display_name); ?></strong><br>
                                    <small style="color: #64748b;">ID: <?php echo esc_html($user->ID); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($travels)): ?>
                                <div style="max-height: 100px; overflow-y: auto;">
                                    <?php foreach (array_slice($travels, 0, 5) as $travel): ?>
                                        <div style="margin-bottom: 8px; padding: 8px; background: #f8fafc; border-radius: 6px;">
                                            <strong style="color: #0f172a;"><?php echo esc_html($travel->post_title); ?></strong>
                                            <?php 
                                            $rating = get_post_meta($travel->ID, '_rating', true);
                                            $date = get_post_meta($travel->ID, '_visit_date', true);
                                            ?>
                                            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                                                <?php if ($rating): ?>
                                                    <?php echo str_repeat('⭐', $rating); ?>
                                                <?php endif; ?>
                                                <?php if ($date): ?>
                                                    • <?php echo date_i18n(get_option('date_format'), strtotime($date)); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($travels) > 5): ?>
                                        <div style="font-size: 12px; color: #64748b; font-style: italic;">
                                            + <?php echo count($travels) - 5; ?> more...
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span style="color: #94a3b8;">No destinations</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($countries)): ?>
                                <?php foreach ($countries as $country): ?>
                                    <span style="display: inline-block; background: #dbeafe; color: #1d4ed8; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin: 2px;">
                                        <?php echo esc_html($country); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color: #94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="color: #2563eb; font-size: 16px;"><?php echo count($travels); ?></strong>
                        </td>
                        <td>
                            <?php if ($last_travel): ?>
                                <span style="color: #64748b;">
                                    <?php echo date_i18n(get_option('date_format'), strtotime($last_travel)); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>" 
                                   class="button button-small"
                                   style="text-decoration: none;">
                                    👤 User
                                </a>
                                <a href="<?php echo admin_url('edit.php?post_type=destination&_user_id=' . $user->ID); ?>" 
                                   class="button button-small"
                                   style="text-decoration: none;">
                                    📍 Travels
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 30px; padding: 20px; background: #f0f9ff; border-left: 4px solid #2563eb; border-radius: 8px;">
        <h3 style="margin: 0 0 10px 0; color: #0369a1;">💡 How It Works</h3>
        <ol style="margin: 0; padding-left: 20px; color: #075985; line-height: 1.8;">
            <li>Members go to <strong>My Travels</strong> page from their dashboard</li>
            <li>They add destinations they've visited with details (date, country, rating, description)</li>
            <li>Travel history appears here for admin to view</li>
            <li>Use this data for membership upgrades, rewards, and marketing insights</li>
        </ol>
    </div>
</div>
