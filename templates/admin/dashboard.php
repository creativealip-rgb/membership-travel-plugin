<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap travelship-admin">
    <h1>✈️ TravelShip Dashboard</h1>

    <!-- Stats Cards -->
    <div class="ts-stats-grid">
        <div class="ts-stat-card ts-stat-tours">
            <div class="ts-stat-icon">🗺️</div>
            <div class="ts-stat-info">
                <span class="ts-stat-number"><?php echo esc_html($stats['active_tours']); ?></span>
                <span class="ts-stat-label">Tour Aktif</span>
            </div>
        </div>
        <div class="ts-stat-card ts-stat-bookings">
            <div class="ts-stat-icon">📋</div>
            <div class="ts-stat-info">
                <span class="ts-stat-number"><?php echo esc_html($stats['total_bookings']); ?></span>
                <span class="ts-stat-label">Booking Bulan Ini</span>
            </div>
        </div>
        <div class="ts-stat-card ts-stat-revenue">
            <div class="ts-stat-icon">💰</div>
            <div class="ts-stat-info">
                <span class="ts-stat-number"><?php echo \TravelShip\Helpers\Utils::format_price($stats['month_revenue']); ?></span>
                <span class="ts-stat-label">Revenue Bulan Ini</span>
            </div>
        </div>
        <div class="ts-stat-card ts-stat-members">
            <div class="ts-stat-icon">👥</div>
            <div class="ts-stat-info">
                <span class="ts-stat-number"><?php echo esc_html($stats['total_members']); ?></span>
                <span class="ts-stat-label">Total Member</span>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="ts-dashboard-row">
        <div class="ts-card ts-chart-card">
            <h2>📊 Statistik Booking (6 Bulan Terakhir)</h2>
            <canvas id="ts-booking-chart" height="300"></canvas>
        </div>
    </div>

    <!-- Two column layout -->
    <div class="ts-dashboard-row ts-two-col">
        <!-- Recent Bookings -->
        <div class="ts-card">
            <h2>📋 Booking Terbaru</h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>User</th>
                        <th>Tour</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_bookings)): ?>
                        <tr><td colspan="5" style="text-align:center;padding:20px;color:#9ca3af;">Belum ada booking</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_bookings as $booking): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=travelship-bookings&action=view&id=' . $booking->id); ?>">
                                        <strong><?php echo esc_html($booking->booking_code); ?></strong>
                                    </a>
                                </td>
                                <td><?php echo esc_html($booking->display_name); ?></td>
                                <td><?php echo esc_html($booking->tour_title); ?></td>
                                <td><?php echo \TravelShip\Helpers\Utils::booking_status_badge($booking->status); ?></td>
                                <td><?php echo \TravelShip\Helpers\Utils::format_price($booking->total_price); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Upcoming Tours -->
        <div class="ts-card">
            <h2>🗓️ Tour Mendatang</h2>
            <?php if (empty($upcoming_tours)): ?>
                <p style="text-align:center;padding:20px;color:#9ca3af;">Tidak ada tour mendatang</p>
            <?php else: ?>
                <?php foreach ($upcoming_tours as $tour): ?>
                    <div class="ts-upcoming-tour-item">
                        <div class="ts-upcoming-tour-info">
                            <strong><?php echo esc_html($tour->title); ?></strong>
                            <span class="ts-text-muted">
                                📍 <?php echo esc_html($tour->destination); ?> &bull;
                                📅 <?php echo \TravelShip\Helpers\Utils::format_date_range($tour->start_date, $tour->end_date); ?>
                            </span>
                        </div>
                        <div class="ts-upcoming-tour-meta">
                            <span class="ts-price"><?php echo \TravelShip\Helpers\Utils::format_price($tour->price); ?></span>
                            <span class="ts-quota">
                                <?php
                                $booked = \TravelShip\DB::get_tour_booked_count($tour->id);
                                echo esc_html($booked . '/' . $tour->max_participants);
                                ?> peserta
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') return;

    const data = <?php echo json_encode($monthly_stats); ?>;
    const labels = data.map(d => d.month);
    const bookings = data.map(d => parseInt(d.total));
    const revenue = data.map(d => parseFloat(d.revenue));

    new Chart(document.getElementById('ts-booking-chart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Jumlah Booking',
                    data: bookings,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderRadius: 8,
                    yAxisID: 'y',
                },
                {
                    label: 'Revenue (Rp)',
                    data: revenue,
                    type: 'line',
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y:  { beginAtZero: true, position: 'left', title: { display: true, text: 'Booking' } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Revenue (Rp)' } }
            }
        }
    });
});
</script>
