<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap travelship-admin">
    <h1 class="wp-heading-inline">📋 Kelola Booking</h1>
    <hr class="wp-header-end">

    <div class="ts-filters">
        <form method="get">
            <input type="hidden" name="page" value="travelship-bookings">
            <select name="status">
                <option value="">Semua Status</option>
                <option value="pending" <?php selected($args['status'], 'pending'); ?>>Menunggu</option>
                <option value="confirmed" <?php selected($args['status'], 'confirmed'); ?>>Dikonfirmasi</option>
                <option value="paid" <?php selected($args['status'], 'paid'); ?>>Sudah Bayar</option>
                <option value="cancelled" <?php selected($args['status'], 'cancelled'); ?>>Dibatalkan</option>
                <option value="completed" <?php selected($args['status'], 'completed'); ?>>Selesai</option>
            </select>
            <input type="text" name="s" placeholder="Cari kode booking..." value="<?php echo esc_attr($args['search']); ?>">
            <button type="submit" class="button">Filter</button>
        </form>
    </div>

    <table class="widefat striped ts-table">
        <thead>
            <tr>
                <th>Kode Booking</th>
                <th>User</th>
                <th>Tour</th>
                <th>Peserta</th>
                <th>Total</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($result['items'])): ?>
                <tr><td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;">Belum ada booking</td></tr>
            <?php else: ?>
                <?php foreach ($result['items'] as $booking): ?>
                    <?php $user = get_userdata($booking->user_id); ?>
                    <tr>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=travelship-bookings&action=view&id=' . $booking->id); ?>">
                                <strong><?php echo esc_html($booking->booking_code); ?></strong>
                            </a>
                        </td>
                        <td>
                            <?php echo $user ? esc_html($user->display_name) : 'User #' . $booking->user_id; ?>
                        </td>
                        <td><?php echo esc_html($booking->tour_title); ?></td>
                        <td><?php echo esc_html($booking->participants); ?> orang</td>
                        <td><strong><?php echo \TravelShip\Helpers\Utils::format_price($booking->total_price); ?></strong></td>
                        <td><?php echo \TravelShip\Helpers\Utils::booking_status_badge($booking->status); ?></td>
                        <td><?php echo \TravelShip\Helpers\Utils::format_date($booking->created_at); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=travelship-bookings&action=view&id=' . $booking->id); ?>" class="button button-small">👁️ Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($result['total_pages'] > 1): ?>
        <div class="ts-pagination">
            <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
                <a href="<?php echo admin_url('admin.php?page=travelship-bookings&paged=' . $i . '&status=' . urlencode($args['status'])); ?>"
                   class="button <?php echo $result['page'] === $i ? 'button-primary' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
