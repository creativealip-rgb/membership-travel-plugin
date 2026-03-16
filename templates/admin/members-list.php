<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap travelship-admin">
    <h1 class="wp-heading-inline">👥 Kelola Member</h1>
    <hr class="wp-header-end">

    <div class="ts-filters">
        <form method="get">
            <input type="hidden" name="page" value="travelship-members">
            <select name="level">
                <option value="">Semua Level</option>
                <option value="basic" <?php selected($args['membership_level'], 'basic'); ?>>Basic</option>
                <option value="silver" <?php selected($args['membership_level'], 'silver'); ?>>Silver</option>
                <option value="gold" <?php selected($args['membership_level'], 'gold'); ?>>Gold</option>
                <option value="platinum" <?php selected($args['membership_level'], 'platinum'); ?>>Platinum</option>
            </select>
            <input type="text" name="s" placeholder="Cari nama, email, telepon..." value="<?php echo esc_attr($args['search']); ?>">
            <button type="submit" class="button">Filter</button>
        </form>
    </div>

    <table class="widefat striped ts-table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Level</th>
                <th>Poin</th>
                <th>Bergabung</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($result['items'])): ?>
                <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">Belum ada member</td></tr>
            <?php else: ?>
                <?php foreach ($result['items'] as $member): ?>
                    <tr>
                        <td><strong><?php echo esc_html($member->display_name); ?></strong></td>
                        <td><?php echo esc_html($member->user_email); ?></td>
                        <td><?php echo esc_html($member->phone ?: '-'); ?></td>
                        <td><?php echo \TravelShip\Helpers\Utils::membership_badge($member->membership_level); ?></td>
                        <td><?php echo number_format($member->points); ?> pts</td>
                        <td><?php echo \TravelShip\Helpers\Utils::format_date($member->joined_at); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=travelship-members&action=view&id=' . $member->id); ?>" class="button button-small">👁️ Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($result['total_pages'] > 1): ?>
        <div class="ts-pagination">
            <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
                <a href="<?php echo admin_url('admin.php?page=travelship-members&paged=' . $i); ?>" class="button <?php echo $result['page'] === $i ? 'button-primary' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
