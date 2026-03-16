<?php if (!defined('ABSPATH')) exit;
$total_spent = 0;
$completed_trips = 0;
if (!empty($bookings['items'])) {
    foreach ($bookings['items'] as $b) {
        if (in_array($b->status, ['paid','completed'])) $total_spent += $b->total_price;
        if ($b->status === 'completed') $completed_trips++;
    }
}
?>
<div class="wrap travelship-admin">
    <h1>👤 Detail Member: <?php echo esc_html($member->display_name); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=travelship-members'); ?>" class="page-title-action">← Kembali</a>
    <hr class="wp-header-end">

    <div class="ts-detail-grid">
        <div class="ts-card">
            <h2>Profil Member</h2>
            <table class="form-table">
                <tr><th>Nama</th><td><?php echo esc_html($member->display_name); ?></td></tr>
                <tr><th>Email</th><td><?php echo esc_html($member->user_email); ?></td></tr>
                <tr><th>Telepon</th><td><?php echo esc_html($member->phone ?: '-'); ?></td></tr>
                <tr><th>Alamat</th><td><?php echo esc_html($member->address ?: '-'); ?></td></tr>
                <tr><th>No. KTP/Passport</th><td><?php echo esc_html($member->id_number ?: '-'); ?></td></tr>
                <tr><th>Kontak Darurat</th><td><?php echo esc_html($member->emergency_contact ?: '-'); ?> <?php echo $member->emergency_phone ? '(' . esc_html($member->emergency_phone) . ')' : ''; ?></td></tr>
                <tr><th>Bergabung Sejak</th><td><?php echo \TravelShip\Helpers\Utils::format_date($member->joined_at); ?></td></tr>
            </table>

            <h3 style="margin-top:20px;">📊 Statistik</h3>
            <div class="ts-stats-grid ts-stats-mini">
                <div class="ts-stat-card"><div class="ts-stat-info"><span class="ts-stat-number"><?php echo $completed_trips; ?></span><span class="ts-stat-label">Trip Selesai</span></div></div>
                <div class="ts-stat-card"><div class="ts-stat-info"><span class="ts-stat-number"><?php echo \TravelShip\Helpers\Utils::format_price($total_spent); ?></span><span class="ts-stat-label">Total Spending</span></div></div>
                <div class="ts-stat-card"><div class="ts-stat-info"><span class="ts-stat-number"><?php echo number_format($member->points); ?></span><span class="ts-stat-label">Poin</span></div></div>
            </div>

            <h3 style="margin-top:20px;">📋 Riwayat Booking</h3>
            <table class="widefat striped">
                <thead><tr><th>Kode</th><th>Tour</th><th>Status</th><th>Total</th><th>Tanggal</th></tr></thead>
                <tbody>
                    <?php if (empty($bookings['items'])): ?>
                        <tr><td colspan="5" style="text-align:center;padding:20px;color:#9ca3af;">Belum ada booking</td></tr>
                    <?php else: ?>
                        <?php foreach ($bookings['items'] as $b): ?>
                            <tr>
                                <td><a href="<?php echo admin_url('admin.php?page=travelship-bookings&action=view&id=' . $b->id); ?>"><?php echo esc_html($b->booking_code); ?></a></td>
                                <td><?php echo esc_html($b->tour_title); ?></td>
                                <td><?php echo \TravelShip\Helpers\Utils::booking_status_badge($b->status); ?></td>
                                <td><?php echo \TravelShip\Helpers\Utils::format_price($b->total_price); ?></td>
                                <td><?php echo \TravelShip\Helpers\Utils::format_date($b->created_at); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div>
            <div class="ts-card">
                <h2>⚡ Kelola Membership</h2>
                <form method="post">
                    <?php wp_nonce_field('travelship_update_member', '_travelship_nonce'); ?>
                    <div class="ts-field">
                        <label>Level Membership</label>
                        <select name="membership_level" style="width:100%;">
                            <option value="basic" <?php selected($member->membership_level, 'basic'); ?>>🔵 Basic</option>
                            <option value="silver" <?php selected($member->membership_level, 'silver'); ?>>⚪ Silver</option>
                            <option value="gold" <?php selected($member->membership_level, 'gold'); ?>>🟡 Gold</option>
                            <option value="platinum" <?php selected($member->membership_level, 'platinum'); ?>>🟣 Platinum</option>
                        </select>
                    </div>
                    <div class="ts-field">
                        <label>Poin Reward</label>
                        <input type="number" name="points" value="<?php echo esc_attr($member->points); ?>" min="0" style="width:100%;">
                    </div>
                    <button type="submit" class="button button-primary" style="width:100%;">💾 Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
