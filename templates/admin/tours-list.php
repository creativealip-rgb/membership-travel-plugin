<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap travelship-admin">
    <h1 class="wp-heading-inline">🗺️ Kelola Tour</h1>
    <a href="<?php echo admin_url('admin.php?page=travelship-tours&action=new'); ?>" class="page-title-action">+ Tambah Tour Baru</a>
    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="ts-filters">
        <form method="get">
            <input type="hidden" name="page" value="travelship-tours">
            <select name="status">
                <option value="">Semua Status</option>
                <option value="draft" <?php selected($args['status'], 'draft'); ?>>Draft</option>
                <option value="published" <?php selected($args['status'], 'published'); ?>>Published</option>
                <option value="cancelled" <?php selected($args['status'], 'cancelled'); ?>>Dibatalkan</option>
                <option value="completed" <?php selected($args['status'], 'completed'); ?>>Selesai</option>
            </select>
            <input type="text" name="s" placeholder="Cari tour..." value="<?php echo esc_attr($args['search']); ?>">
            <button type="submit" class="button">Filter</button>
        </form>
    </div>

    <!-- Tours Table -->
    <table class="widefat striped ts-table">
        <thead>
            <tr>
                <th>Tour</th>
                <th>Destinasi</th>
                <th>Tanggal</th>
                <th>Harga</th>
                <th>Kuota</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($result['items'])): ?>
                <tr><td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">Belum ada tour. <a href="<?php echo admin_url('admin.php?page=travelship-tours&action=new'); ?>">Buat tour baru</a></td></tr>
            <?php else: ?>
                <?php foreach ($result['items'] as $tour): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=travelship-tours&action=edit&id=' . $tour->id); ?>">
                                    <?php echo esc_html($tour->title); ?>
                                </a>
                            </strong>
                        </td>
                        <td>📍 <?php echo esc_html($tour->destination); ?></td>
                        <td><?php echo \TravelShip\Helpers\Utils::format_date_range($tour->start_date, $tour->end_date); ?></td>
                        <td><?php echo \TravelShip\Helpers\Utils::format_price($tour->price); ?></td>
                        <td>
                            <?php
                            $booked = \TravelShip\DB::get_tour_booked_count($tour->id);
                            $percent = $tour->max_participants > 0 ? ($booked / $tour->max_participants * 100) : 0;
                            ?>
                            <div class="ts-quota-bar">
                                <div class="ts-quota-fill" style="width:<?php echo min(100, $percent); ?>%"></div>
                            </div>
                            <small><?php echo $booked; ?>/<?php echo $tour->max_participants; ?></small>
                        </td>
                        <td><?php echo \TravelShip\Helpers\Utils::tour_status_badge($tour->status); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=travelship-tours&action=edit&id=' . $tour->id); ?>" class="button button-small" title="Edit">✏️</a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travelship-tours&duplicate=' . $tour->id), 'travelship_duplicate_tour'); ?>" class="button button-small" title="Duplikat" onclick="return confirm('Duplikat tour ini?')">📋</a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=travelship-tours&delete=' . $tour->id), 'travelship_delete_tour'); ?>" class="button button-small ts-btn-danger" title="Hapus" onclick="return confirm('Yakin hapus tour ini?')">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($result['total_pages'] > 1): ?>
        <div class="ts-pagination">
            <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
                <a href="<?php echo admin_url('admin.php?page=travelship-tours&paged=' . $i . '&status=' . urlencode($args['status']) . '&s=' . urlencode($args['search'])); ?>"
                   class="button <?php echo $result['page'] === $i ? 'button-primary' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
