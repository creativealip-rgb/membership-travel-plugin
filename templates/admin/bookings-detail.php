<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap travelship-admin">
    <h1>📋 Detail Booking #<?php echo esc_html($booking->booking_code); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=travelship-bookings'); ?>" class="page-title-action">← Kembali</a>
    <hr class="wp-header-end">

    <div class="ts-detail-grid">
        <!-- Booking Info -->
        <div class="ts-card">
            <h2>Informasi Booking</h2>
            <table class="form-table">
                <tr>
                    <th>Kode Booking</th>
                    <td><strong><?php echo esc_html($booking->booking_code); ?></strong></td>
                </tr>
                <tr>
                    <th>Tour</th>
                    <td>
                        <?php echo esc_html($booking->tour_title); ?><br>
                        <small>📍 <?php echo esc_html($booking->destination); ?> &bull;
                        📅 <?php echo \TravelShip\Helpers\Utils::format_date_range($booking->start_date, $booking->end_date); ?></small>
                    </td>
                </tr>
                <tr>
                    <th>Jumlah Peserta</th>
                    <td><?php echo esc_html($booking->participants); ?> orang</td>
                </tr>
                <tr>
                    <th>Harga per Orang</th>
                    <td><?php echo \TravelShip\Helpers\Utils::format_price($booking->tour_price); ?></td>
                </tr>
                <tr>
                    <th>Total Harga</th>
                    <td><strong style="font-size:18px;color:#10b981;"><?php echo \TravelShip\Helpers\Utils::format_price($booking->total_price); ?></strong></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?php echo \TravelShip\Helpers\Utils::booking_status_badge($booking->status); ?></td>
                </tr>
                <tr>
                    <th>Catatan</th>
                    <td><?php echo esc_html($booking->notes ?: '-'); ?></td>
                </tr>
                <tr>
                    <th>Metode Bayar</th>
                    <td><?php echo esc_html($booking->payment_method ?: '-'); ?></td>
                </tr>
                <?php if ($booking->payment_proof): ?>
                    <tr>
                        <th>Bukti Bayar</th>
                        <td>
                            <a href="<?php echo esc_url(wp_get_attachment_url($booking->payment_proof)); ?>" target="_blank">
                                <?php echo wp_get_attachment_image($booking->payment_proof, 'medium'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th>Tanggal Order</th>
                    <td><?php echo \TravelShip\Helpers\Utils::format_date($booking->created_at, 'd M Y H:i'); ?></td>
                </tr>
                <?php if ($booking->paid_at): ?>
                    <tr>
                        <th>Tanggal Bayar</th>
                        <td><?php echo \TravelShip\Helpers\Utils::format_date($booking->paid_at, 'd M Y H:i'); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- User Info + Update Status -->
        <div>
            <div class="ts-card">
                <h2>Informasi User</h2>
                <table class="form-table">
                    <tr>
                        <th>Nama</th>
                        <td><?php echo $user ? esc_html($user->display_name) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo $user ? esc_html($user->user_email) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th>Telepon</th>
                        <td><?php echo $member ? esc_html($member->phone ?: '-') : '-'; ?></td>
                    </tr>
                    <tr>
                        <th>No. KTP</th>
                        <td><?php echo $member ? esc_html($member->id_number ?: '-') : '-'; ?></td>
                    </tr>
                    <tr>
                        <th>Level</th>
                        <td><?php echo $member ? \TravelShip\Helpers\Utils::membership_badge($member->membership_level) : '-'; ?></td>
                    </tr>
                </table>
            </div>

            <div class="ts-card">
                <h2>⚡ Update Status</h2>
                <form method="post">
                    <?php wp_nonce_field('travelship_update_booking_status', '_travelship_nonce'); ?>
                    <input type="hidden" name="booking_id" value="<?php echo esc_attr($booking->id); ?>">
                    <input type="hidden" name="update_status" value="1">
                    <div class="ts-field">
                        <select name="new_status" style="width:100%;">
                            <option value="pending" <?php selected($booking->status, 'pending'); ?>>⏳ Menunggu</option>
                            <option value="confirmed" <?php selected($booking->status, 'confirmed'); ?>>✅ Dikonfirmasi</option>
                            <option value="paid" <?php selected($booking->status, 'paid'); ?>>💰 Sudah Bayar</option>
                            <option value="cancelled" <?php selected($booking->status, 'cancelled'); ?>>❌ Dibatalkan</option>
                            <option value="completed" <?php selected($booking->status, 'completed'); ?>>🏁 Selesai</option>
                        </select>
                    </div>
                    <button type="submit" class="button button-primary" style="width:100%;" onclick="return confirm('Ubah status booking ini?')">
                        Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
