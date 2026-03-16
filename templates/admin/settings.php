<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap travelship-admin">
    <h1>⚙️ Pengaturan TravelShip</h1>

    <form method="post" class="ts-form">
        <?php wp_nonce_field('travelship_save_settings', '_travelship_nonce'); ?>

        <div class="ts-settings-tabs">
            <nav class="ts-tabs-nav">
                <a href="#tab-general" class="ts-tab active" data-tab="tab-general">🏢 Umum</a>
                <a href="#tab-booking" class="ts-tab" data-tab="tab-booking">📋 Booking</a>
                <a href="#tab-payment" class="ts-tab" data-tab="tab-payment">💳 Pembayaran</a>
                <a href="#tab-email" class="ts-tab" data-tab="tab-email">📧 Email</a>
            </nav>

            <!-- General Tab -->
            <div id="tab-general" class="ts-tab-content active">
                <div class="ts-card">
                    <h2>Informasi Bisnis</h2>
                    <div class="ts-field">
                        <label for="business_name">Nama Bisnis</label>
                        <input type="text" id="business_name" name="business_name" value="<?php echo esc_attr($settings['business_name']); ?>">
                    </div>
                    <div class="ts-field">
                        <label for="business_phone">Telepon</label>
                        <input type="text" id="business_phone" name="business_phone" value="<?php echo esc_attr($settings['business_phone']); ?>" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="ts-field">
                        <label for="business_email">Email</label>
                        <input type="email" id="business_email" name="business_email" value="<?php echo esc_attr($settings['business_email']); ?>">
                    </div>
                    <div class="ts-field">
                        <label for="business_address">Alamat</label>
                        <textarea id="business_address" name="business_address" rows="3"><?php echo esc_textarea($settings['business_address']); ?></textarea>
                    </div>
                    <div class="ts-field">
                        <label for="currency">Mata Uang</label>
                        <select id="currency" name="currency">
                            <option value="IDR" <?php selected($settings['currency'], 'IDR'); ?>>IDR (Rupiah)</option>
                            <option value="USD" <?php selected($settings['currency'], 'USD'); ?>>USD (Dollar)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Booking Tab -->
            <div id="tab-booking" class="ts-tab-content">
                <div class="ts-card">
                    <h2>Pengaturan Booking</h2>
                    <div class="ts-field">
                        <label>
                            <input type="checkbox" name="auto_confirm" value="1" <?php checked($settings['auto_confirm'], 1); ?>>
                            Auto-confirm booking baru (tanpa review admin)
                        </label>
                    </div>
                    <div class="ts-field">
                        <label for="payment_deadline">Batas Waktu Pembayaran (jam)</label>
                        <input type="number" id="payment_deadline" name="payment_deadline" min="1" value="<?php echo esc_attr($settings['payment_deadline']); ?>">
                        <p class="description">Booking akan otomatis dibatalkan jika tidak dibayar dalam waktu ini.</p>
                    </div>
                </div>
            </div>

            <!-- Payment Tab -->
            <div id="tab-payment" class="ts-tab-content">
                <div class="ts-card">
                    <h2>Informasi Rekening Bank</h2>
                    <p class="description">Informasi ini akan ditampilkan ke user saat mereka perlu melakukan transfer.</p>
                    <div class="ts-field">
                        <label for="bank_name">Nama Bank</label>
                        <input type="text" id="bank_name" name="bank_name" value="<?php echo esc_attr($settings['bank_name']); ?>" placeholder="BCA / Mandiri / BNI / BRI">
                    </div>
                    <div class="ts-field">
                        <label for="bank_account">Nomor Rekening</label>
                        <input type="text" id="bank_account" name="bank_account" value="<?php echo esc_attr($settings['bank_account']); ?>">
                    </div>
                    <div class="ts-field">
                        <label for="bank_holder">Atas Nama</label>
                        <input type="text" id="bank_holder" name="bank_holder" value="<?php echo esc_attr($settings['bank_holder']); ?>">
                    </div>
                </div>
            </div>

            <!-- Email Tab -->
            <div id="tab-email" class="ts-tab-content">
                <div class="ts-card">
                    <h2>Notifikasi Email</h2>
                    <div class="ts-field">
                        <label>
                            <input type="checkbox" name="enable_email_booking" value="1" <?php checked($settings['enable_email_booking'], 1); ?>>
                            Kirim email konfirmasi saat booking baru
                        </label>
                    </div>
                    <div class="ts-field">
                        <label>
                            <input type="checkbox" name="enable_email_status" value="1" <?php checked($settings['enable_email_status'], 1); ?>>
                            Kirim email saat status booking berubah
                        </label>
                    </div>
                    <div class="ts-field">
                        <label>
                            <input type="checkbox" name="enable_email_reminder" value="1" <?php checked($settings['enable_email_reminder'], 1); ?>>
                            Kirim email reminder sebelum trip
                        </label>
                    </div>
                    <div class="ts-field">
                        <label for="reminder_days_before">Reminder dikirim H-</label>
                        <input type="number" id="reminder_days_before" name="reminder_days_before" min="1" max="14" value="<?php echo esc_attr($settings['reminder_days_before']); ?>"> hari sebelum berangkat
                    </div>
                </div>
            </div>
        </div>

        <p class="submit">
            <button type="submit" class="button button-primary button-large">💾 Simpan Pengaturan</button>
        </p>
    </form>
</div>
