<?php
/**
 * Verify Payments - Admin Menu
 * Unified verification dashboard for tour bookings and member upgrade payments
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Verify_Payments {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_tmpb_bulk_update_booking_status', [$this, 'handle_bulk_update']);
    }

    /**
     * Add admin menu (as submenu under Tours)
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=tour',
            __('Review Pembayaran', 'travel-membership-pro'),
            __('Review Pembayaran', 'travel-membership-pro'),
            'manage_options',
            'tmp-verify-payments',
            [$this, 'render_verify_page']
        );
    }

    public function render_verify_page() {
        $status_filter = sanitize_text_field($_GET['booking_status'] ?? 'needs_review');
        $search_term = sanitize_text_field($_GET['s'] ?? '');

        $status_map = [
            'needs_review' => ['payment_uploaded'],
            'paid'         => ['paid'],
            'confirmed'    => ['confirmed'],
            'pending'      => ['pending_payment'],
            'done'         => ['completed', 'cancelled', 'refunded'],
            'all'          => [],
        ];

        $booking_args = [
            'post_type'      => 'tour_booking',
            'post_status'    => 'any',
            'posts_per_page' => 100,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [],
        ];

        if (!empty($status_map[$status_filter])) {
            $booking_args['meta_query'][] = [
                'key'     => '_booking_status',
                'value'   => $status_map[$status_filter],
                'compare' => 'IN',
            ];
        }

        if ($search_term !== '') {
            $booking_args['s'] = $search_term;
        }

        $bookings = get_posts($booking_args);

        if ($search_term !== '') {
            $bookings = array_values(array_filter($bookings, function ($booking) use ($search_term) {
                $haystacks = [
                    $booking->post_title,
                    get_post_meta($booking->ID, '_booking_code', true),
                    get_post_meta($booking->ID, '_customer_name', true),
                    get_post_meta($booking->ID, '_customer_email', true),
                ];

                foreach ($haystacks as $value) {
                    if ($value && stripos((string) $value, $search_term) !== false) {
                        return true;
                    }
                }

                return false;
            }));
        }

        $stats = [
            'payment_uploaded' => 0,
            'paid'             => 0,
            'confirmed'        => 0,
            'pending_payment'  => 0,
        ];

        $all_for_stats = get_posts([
            'post_type'      => 'tour_booking',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);

        foreach ($all_for_stats as $booking_id) {
            $status = get_post_meta($booking_id, '_booking_status', true);
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }

        $member_users = get_users(['meta_key' => '_tmp_pending_tier']);
        ?>
        <div class="wrap">
            <h1 style="margin-bottom:16px;">✅ Review Pembayaran</h1>
            <p style="max-width:900px; color:#475569; font-size:14px; line-height:1.7; margin-bottom:18px;">
                Dashboard ini buat cek payment proof tour, update status booking, dan pantau payment yang udah masuk tanpa buka booking satu-satu.
            </p>

            <?php $this->render_notice(); ?>

            <div style="display:grid; grid-template-columns:repeat(4, minmax(160px,1fr)); gap:16px; margin:18px 0 24px; max-width:1100px;">
                <?php echo $this->render_stat_card('Perlu Review', $stats['payment_uploaded'], '#1d4ed8', '#dbeafe', admin_url('edit.php?post_type=tour&page=tmp-verify-payments&booking_status=needs_review')); ?>
                <?php echo $this->render_stat_card('Lunas', $stats['paid'], '#047857', '#d1fae5', admin_url('edit.php?post_type=tour&page=tmp-verify-payments&booking_status=paid')); ?>
                <?php echo $this->render_stat_card('Dikonfirmasi', $stats['confirmed'], '#166534', '#dcfce7', admin_url('edit.php?post_type=tour&page=tmp-verify-payments&booking_status=confirmed')); ?>
                <?php echo $this->render_stat_card('Menunggu Bayar', $stats['pending_payment'], '#92400e', '#fef3c7', admin_url('edit.php?post_type=tour&page=tmp-verify-payments&booking_status=pending')); ?>
            </div>

            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:18px; margin-bottom:24px; max-width:1200px; box-shadow:0 8px 28px rgba(15,23,42,.05);">
                <form method="get" action="" style="display:grid; gap:14px;">
                    <input type="hidden" name="post_type" value="tour">
                    <input type="hidden" name="page" value="tmp-verify-payments">
                    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:end;">
                        <div style="min-width:220px;">
                            <label for="booking_status" style="display:block; font-weight:600; margin-bottom:6px;">Filter status</label>
                            <select name="booking_status" id="booking_status" style="min-width:220px; width:100%;">
                                <?php foreach ([
                                    'needs_review' => 'Perlu Review (bukti masuk)',
                                    'paid' => 'Lunas',
                                    'confirmed' => 'Dikonfirmasi',
                                    'pending' => 'Menunggu Pembayaran',
                                    'done' => 'Selesai / Dibatalkan / Refund',
                                    'all' => 'Semua Status',
                                ] as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($status_filter, $value); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="min-width:280px; flex:1;">
                            <label for="payment-search" style="display:block; font-weight:600; margin-bottom:6px;">Cari booking / customer / code</label>
                            <input type="search" id="payment-search" name="s" value="<?php echo esc_attr($search_term); ?>" placeholder="Contoh: Cynthia, TMPB-, Labuan Bajo" style="width:100%;">
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <button type="submit" class="button button-primary">Terapkan</button>
                            <a href="<?php echo esc_url(admin_url('edit.php?post_type=tour&page=tmp-verify-payments')); ?>" class="button">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; max-width:1280px; box-shadow:0 8px 28px rgba(15,23,42,.05); margin-bottom:28px;">
                <div style="padding:18px 18px 10px; border-bottom:1px solid #e2e8f0;">
                    <h2 style="margin:0; font-size:18px;">Antrian Verifikasi Booking</h2>
                    <p style="margin:8px 0 0; color:#64748b;">Total tampil: <?php echo count($bookings); ?> booking</p>
                </div>

                <?php if (empty($bookings)) : ?>
                    <div style="padding:28px 18px; color:#64748b;">Nggak ada booking yang cocok dengan filter ini.</div>
                <?php else : ?>
                    <div style="padding:16px 18px; border-bottom:1px solid #e2e8f0; background:#f8fafc; display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                        <?php wp_nonce_field('tmpb_bulk_update_booking_status', 'tmpb_bulk_nonce'); ?>
                        <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                            <input type="checkbox" id="tmpb-select-all-bookings">
                            Pilih semua di halaman ini
                        </label>
                        <select id="tmpb-bulk-status" style="min-width:220px;">
                            <option value="">Pilih aksi massal...</option>
                            <option value="paid">Tandai sebagai Lunas</option>
                            <option value="confirmed">Tandai sebagai Dikonfirmasi</option>
                            <option value="completed">Tandai sebagai Selesai</option>
                            <option value="cancelled">Tandai sebagai Dibatalkan</option>
                            <option value="refunded">Tandai sebagai Refund</option>
                            <option value="trash">Pindahkan ke Trash</option>
                        </select>
                        <input type="text" id="tmpb-bulk-note" placeholder="Catatan admin untuk semua booking terpilih (opsional)" style="min-width:320px; flex:1;">
                        <button type="button" id="tmpb-bulk-apply" class="button button-primary">Proses yang Dipilih</button>
                    </div>
                    <div style="overflow:auto;">
                        <table class="wp-list-table widefat striped" style="border:0; min-width:1230px;">
                            <thead>
                                <tr>
                                    <th style="width:42px;"><input type="checkbox" id="tmpb-select-all-bookings-top"></th>
                                    <th style="width:180px;">Booking</th>
                                    <th style="width:220px;">Traveler</th>
                                    <th style="width:220px;">Tour</th>
                                    <th style="width:120px;">Travel Date</th>
                                    <th style="width:110px;">Total</th>
                                    <th style="width:140px;">Status</th>
                                    <th style="width:190px;">Payment Proof</th>
                                    <th style="width:300px;">Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking) : ?>
                                    <?php echo $this->render_booking_row($booking); ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <script>
                    (function(){
                        const master=document.getElementById('tmpb-select-all-bookings');
                        const masterTop=document.getElementById('tmpb-select-all-bookings-top');
                        const apply=document.getElementById('tmpb-bulk-apply');
                        const boxes=Array.from(document.querySelectorAll('.tmpb-booking-checkbox'));
                        const sync=(checked)=>boxes.forEach(box=>{box.checked=checked;});
                        if(master){ master.addEventListener('change', ()=>{ sync(master.checked); if(masterTop){ masterTop.checked=master.checked; } }); }
                        if(masterTop){ masterTop.addEventListener('change', ()=>{ sync(masterTop.checked); if(master){ master.checked=masterTop.checked; } }); }
                        if(apply){ apply.addEventListener('click', ()=>{
                            const selected=boxes.filter(box=>box.checked).map(box=>box.value);
                            const bulkStatus=document.getElementById('tmpb-bulk-status');
                            const bulkNote=document.getElementById('tmpb-bulk-note');
                            const nonce=document.getElementById('tmpb_bulk_nonce');
                            if(!selected.length || !bulkStatus || !bulkStatus.value || !nonce){ alert('Pilih booking dan bulk action dulu.'); return; }
                            const form=document.createElement('form');
                            form.method='post';
                            form.action=<?php echo wp_json_encode(admin_url('admin-post.php')); ?>;
                            const fields={
                                action:'tmpb_bulk_update_booking_status',
                                redirect_to:<?php echo wp_json_encode(admin_url('edit.php?post_type=tour&page=tmp-verify-payments&booking_status=' . $status_filter . ($search_term !== '' ? '&s=' . rawurlencode($search_term) : ''))); ?>,
                                tmpb_bulk_nonce:nonce.value,
                                bulk_status:bulkStatus.value,
                                bulk_note:bulkNote ? bulkNote.value : ''
                            };
                            Object.keys(fields).forEach((key)=>{
                                const input=document.createElement('input');
                                input.type='hidden';
                                input.name=key;
                                input.value=fields[key];
                                form.appendChild(input);
                            });
                            selected.forEach((id)=>{
                                const input=document.createElement('input');
                                input.type='hidden';
                                input.name='booking_ids[]';
                                input.value=id;
                                form.appendChild(input);
                            });
                            document.body.appendChild(form);
                            form.submit();
                        }); }
                    })();
                    </script>
                <?php endif; ?>
            </div>

            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:18px; max-width:1280px; box-shadow:0 8px 28px rgba(15,23,42,.05);">
                <h2 style="margin:0 0 14px; font-size:18px;">Pembayaran Upgrade Member</h2>
                <?php if (empty($member_users)) : ?>
                    <div style="padding:6px 0; color:#64748b;">Nggak ada pending membership payment sekarang.</div>
                <?php else : ?>
                    <table class="wp-list-table widefat striped" style="border:1px solid #e2e8f0;">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Tier</th>
                                <th>Order ID</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($member_users as $user) :
                                $tier = get_user_meta($user->ID, '_tmp_pending_tier', true);
                                $order_id = get_user_meta($user->ID, '_tmp_pending_order_id', true);
                                $payments = get_user_meta($user->ID, '_tmp_manual_payments', false);
                                $last_payment = end($payments);
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html($user->display_name); ?></strong></td>
                                    <td><?php echo esc_html($user->user_email); ?></td>
                                    <td><?php echo esc_html(ucfirst((string) $tier)); ?></td>
                                    <td><?php echo esc_html((string) $order_id); ?></td>
                                    <td><?php echo isset($last_payment['submitted_at']) ? esc_html($last_payment['submitted_at']) : 'N/A'; ?></td>
                                    <td><a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $user->ID)); ?>" class="button button-primary">View User</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_notice() {
        $notice = sanitize_text_field($_GET['tmpb_notice'] ?? '');

        if ($notice === 'status_updated') {
            echo '<div class="notice notice-success is-dismissible"><p>Status booking berhasil diupdate.</p></div>';
        } elseif ($notice === 'bulk_updated') {
            $count = absint($_GET['updated_count'] ?? 0);
            echo '<div class="notice notice-success is-dismissible"><p>Bulk action selesai. ' . esc_html((string) $count) . ' booking diproses.</p></div>';
        } elseif ($notice === 'bulk_trashed') {
            $count = absint($_GET['updated_count'] ?? 0);
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html((string) $count) . ' booking dipindah ke Trash.</p></div>';
        } elseif ($notice === 'invalid_status') {
            echo '<div class="notice notice-error is-dismissible"><p>Status yang dipilih tidak valid.</p></div>';
        } elseif ($notice === 'bulk_invalid') {
            echo '<div class="notice notice-error is-dismissible"><p>Bulk action gagal. Pilih booking dan action yang valid dulu.</p></div>';
        }
    }

    public function handle_bulk_update() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'travel-membership-pro'));
        }

        if (!wp_verify_nonce($_POST['tmpb_bulk_nonce'] ?? '', 'tmpb_bulk_update_booking_status')) {
            wp_die(__('Invalid request', 'travel-membership-pro'));
        }

        $booking_ids = array_map('absint', (array) ($_POST['booking_ids'] ?? []));
        $booking_ids = array_values(array_filter($booking_ids));
        $bulk_status = sanitize_text_field($_POST['bulk_status'] ?? '');
        $bulk_note = sanitize_textarea_field($_POST['bulk_note'] ?? '');
        $redirect_to = esc_url_raw($_POST['redirect_to'] ?? admin_url('edit.php?post_type=tour&page=tmp-verify-payments'));

        $valid_statuses = ['paid', 'confirmed', 'completed', 'cancelled', 'refunded', 'trash'];
        if (empty($booking_ids) || !in_array($bulk_status, $valid_statuses, true)) {
            wp_safe_redirect(add_query_arg(['tmpb_notice' => 'bulk_invalid'], $redirect_to));
            exit;
        }

        $updated = 0;
        $manager = class_exists('TMP_Booking_Manager') ? new TMP_Booking_Manager() : null;

        foreach ($booking_ids as $booking_id) {
            $booking = get_post($booking_id);
            if (!$booking || $booking->post_type !== 'tour_booking') {
                continue;
            }

            if ($bulk_status === 'trash') {
                $trashed = wp_trash_post($booking_id);
                if ($trashed) {
                    $updated++;
                }
                continue;
            }

            if ($manager) {
                $manager->update_status($booking_id, $bulk_status);
                if ($bulk_note !== '') {
                    update_post_meta($booking_id, '_latest_admin_note', $bulk_note);
                    $history = get_post_meta($booking_id, '_admin_status_history', true);
                    if (!is_array($history)) {
                        $history = [];
                    }
                    $history[] = [
                        'status' => $bulk_status,
                        'note' => $bulk_note,
                        'user_id' => get_current_user_id(),
                        'at' => current_time('mysql'),
                    ];
                    update_post_meta($booking_id, '_admin_status_history', $history);
                }
                $updated++;
            }
        }

        $notice = $bulk_status === 'trash' ? 'bulk_trashed' : 'bulk_updated';
        wp_safe_redirect(add_query_arg(['tmpb_notice' => $notice, 'updated_count' => $updated], $redirect_to));
        exit;
    }

    private function render_stat_card($label, $value, $color, $bg, $url) {
        return '<a href="' . esc_url($url) . '" style="display:block; text-decoration:none; background:' . esc_attr($bg) . '; color:' . esc_attr($color) . '; border-radius:16px; padding:16px 18px; border:1px solid rgba(15,23,42,.08); box-shadow:0 6px 16px rgba(15,23,42,.04);"><div style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; opacity:.82;">' . esc_html($label) . '</div><div style="font-size:32px; font-weight:800; line-height:1.15; margin-top:6px;">' . esc_html((string) $value) . '</div></a>';
    }

    private function render_booking_row($booking) {
        $booking_id = $booking->ID;
        $booking_code = get_post_meta($booking_id, '_booking_code', true);
        $customer_name = get_post_meta($booking_id, '_customer_name', true);
        $customer_email = get_post_meta($booking_id, '_customer_email', true);
        $tour_id = absint(get_post_meta($booking_id, '_tour_id', true));
        $tour_title = $tour_id ? get_the_title($tour_id) : 'Tour tidak ditemukan';
        $travel_date = get_post_meta($booking_id, '_travel_date', true);
        $total = absint(get_post_meta($booking_id, '_total_amount', true));
        $status = get_post_meta($booking_id, '_booking_status', true);
        $proof_id = absint(get_post_meta($booking_id, '_payment_proof', true));
        $proof_url = $proof_id ? wp_get_attachment_url($proof_id) : '';
        $uploaded_at = get_post_meta($booking_id, '_payment_uploaded_at', true);
        $latest_admin_note = get_post_meta($booking_id, '_latest_admin_note', true);

        ob_start();
        ?>
        <tr>
            <td><input type="checkbox" class="tmpb-booking-checkbox" name="booking_ids[]" value="<?php echo esc_attr((string) $booking_id); ?>"></td>
            <td>
                <strong>#<?php echo esc_html((string) $booking_id); ?></strong><br>
                <span style="color:#64748b;"><?php echo esc_html($booking_code ?: '—'); ?></span><br>
                <a href="<?php echo esc_url(admin_url('post.php?post=' . $booking_id . '&action=edit')); ?>">Buka detail</a>
            </td>
            <td>
                <strong><?php echo esc_html($customer_name ?: 'Traveler'); ?></strong><br>
                <a href="mailto:<?php echo esc_attr($customer_email); ?>"><?php echo esc_html($customer_email ?: '—'); ?></a>
                <?php if ($latest_admin_note) : ?>
                    <div style="margin-top:8px; padding:8px 10px; background:#f8fafc; border-radius:10px; color:#475569; font-size:12px; line-height:1.55;">
                        <strong>Catatan admin:</strong><br><?php echo esc_html($latest_admin_note); ?>
                    </div>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($tour_id) : ?>
                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $tour_id . '&action=edit')); ?>"><strong><?php echo esc_html($tour_title); ?></strong></a>
                <?php else : ?>
                    <strong><?php echo esc_html($tour_title); ?></strong>
                <?php endif; ?>
            </td>
            <td>
                <?php echo $travel_date ? esc_html(wp_date(get_option('date_format'), strtotime($travel_date))) : '—'; ?>
                <?php if ($uploaded_at) : ?>
                    <div style="margin-top:6px; color:#64748b; font-size:12px;">Upload: <?php echo esc_html($uploaded_at); ?></div>
                <?php endif; ?>
            </td>
            <td><strong>Rp <?php echo esc_html(number_format($total, 0, ',', '.')); ?></strong></td>
            <td><?php $this->render_status_badge($status); ?></td>
            <td>
                <?php if ($proof_url) : ?>
                    <a href="<?php echo esc_url($proof_url); ?>" target="_blank" rel="noopener" style="display:inline-block; margin-bottom:8px;">
                        <img src="<?php echo esc_url($proof_url); ?>" alt="Bukti pembayaran" style="width:120px; height:80px; object-fit:cover; border-radius:10px; border:1px solid #cbd5e1;">
                    </a><br>
                    <a href="<?php echo esc_url($proof_url); ?>" target="_blank" rel="noopener">Lihat gambar</a>
                <?php else : ?>
                    <span style="color:#94a3b8;">Belum upload</span>
                <?php endif; ?>
            </td>
            <td>
                <?php echo $this->render_quick_action_form($booking_id, $status); ?>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }

    private function render_quick_action_form($booking_id, $status) {
        $transitions = [
            'pending_payment' => ['payment_uploaded', 'paid', 'cancelled'],
            'payment_uploaded' => ['paid', 'confirmed', 'cancelled'],
            'paid' => ['confirmed', 'cancelled', 'refunded'],
            'confirmed' => ['completed', 'cancelled', 'refunded'],
            'completed' => ['refunded'],
            'cancelled' => ['pending_payment'],
            'refunded' => [],
        ];

        $labels = [
            'payment_uploaded' => 'Bukti masuk',
            'paid' => 'Lunas',
            'confirmed' => 'Dikonfirmasi',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'pending_payment' => 'Menunggu pembayaran',
            'refunded' => 'Refund',
        ];

        $options = $transitions[$status] ?? [];

        ob_start();
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:grid; gap:8px;">
            <?php wp_nonce_field('tmpb_admin_update_status_' . $booking_id, 'tmpb_admin_status_nonce'); ?>
            <input type="hidden" name="action" value="tmpb_admin_update_booking_status">
            <input type="hidden" name="booking_id" value="<?php echo esc_attr((string) $booking_id); ?>">
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr(admin_url('edit.php?post_type=tour&page=tmp-verify-payments&booking_status=' . sanitize_text_field($_GET['booking_status'] ?? 'needs_review') . '&tmpb_notice=status_updated')); ?>">
            <select name="new_status" <?php disabled(empty($options)); ?> style="width:100%;">
                <?php foreach ($options as $next_status) : ?>
                    <option value="<?php echo esc_attr($next_status); ?>"><?php echo esc_html($labels[$next_status] ?? $next_status); ?></option>
                <?php endforeach; ?>
            </select>
            <textarea name="admin_note" rows="2" placeholder="Catatan admin (opsional)" style="width:100%;"></textarea>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button type="submit" class="button button-primary" <?php disabled(empty($options)); ?>>Update status</button>
                <a href="<?php echo esc_url(admin_url('post.php?post=' . $booking_id . '&action=edit')); ?>" class="button">Lihat detail</a>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    private function render_status_badge($status) {
        $map = [
            'pending_payment' => ['Menunggu pembayaran', '#92400e', '#fef3c7'],
            'payment_uploaded' => ['Bukti masuk', '#1d4ed8', '#dbeafe'],
            'paid' => ['Lunas', '#065f46', '#d1fae5'],
            'confirmed' => ['Dikonfirmasi', '#166534', '#dcfce7'],
            'completed' => ['Selesai', '#166534', '#dcfce7'],
            'cancelled' => ['Dibatalkan', '#b91c1c', '#fee2e2'],
            'refunded' => ['Refund', '#6b21a8', '#f3e8ff'],
        ];

        [$label, $color, $bg] = $map[$status] ?? [ucwords(str_replace('_', ' ', (string) $status)), '#334155', '#e2e8f0'];

        echo '<span style="display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:700;color:' . esc_attr($color) . ';background:' . esc_attr($bg) . ';">' . esc_html($label) . '</span>';
    }
}
