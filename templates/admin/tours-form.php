<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap travelship-admin">
    <h1><?php echo $tour ? '✏️ Edit Tour' : '➕ Tambah Tour Baru'; ?></h1>

    <form method="post" class="ts-form">
        <?php wp_nonce_field('travelship_save_tour', '_travelship_nonce'); ?>
        <?php if ($tour): ?>
            <input type="hidden" name="tour_id" value="<?php echo esc_attr($tour->id); ?>">
        <?php endif; ?>

        <div class="ts-form-grid">
            <!-- Left Column -->
            <div class="ts-form-main">
                <div class="ts-card">
                    <h3>Informasi Utama</h3>

                    <div class="ts-field">
                        <label for="title">Nama Tour <span class="required">*</span></label>
                        <input type="text" id="title" name="title" required
                               value="<?php echo esc_attr($tour->title ?? ''); ?>" placeholder="Contoh: Explore Bali 5D4N">
                    </div>

                    <div class="ts-field">
                        <label for="destination">Destinasi</label>
                        <input type="text" id="destination" name="destination"
                               value="<?php echo esc_attr($tour->destination ?? ''); ?>" placeholder="Contoh: Bali, Indonesia">
                    </div>

                    <div class="ts-field">
                        <label for="description">Deskripsi</label>
                        <?php
                        wp_editor($tour->description ?? '', 'description', [
                            'textarea_name' => 'description',
                            'textarea_rows' => 10,
                            'media_buttons' => true,
                        ]);
                        ?>
                    </div>
                </div>

                <div class="ts-card">
                    <h3>Itinerary</h3>
                    <div class="ts-field">
                        <label for="itinerary">Detail Perjalanan per Hari</label>
                        <?php
                        wp_editor($tour->itinerary ?? '', 'itinerary', [
                            'textarea_name' => 'itinerary',
                            'textarea_rows' => 12,
                            'media_buttons' => false,
                        ]);
                        ?>
                        <p class="description">Gunakan heading untuk setiap hari. Contoh: <strong>Hari 1 - Kedatangan di Bali</strong></p>
                    </div>
                </div>

                <div class="ts-card">
                    <h3>Include & Exclude</h3>
                    <div class="ts-field-row">
                        <div class="ts-field ts-half">
                            <label for="includes">Yang Termasuk ✅</label>
                            <textarea id="includes" name="includes" rows="6" placeholder="Satu item per baris:&#10;- Hotel bintang 4&#10;- Transportasi AC&#10;- Makan 3x sehari"><?php echo esc_textarea($tour->includes ?? ''); ?></textarea>
                        </div>
                        <div class="ts-field ts-half">
                            <label for="excludes">Yang Tidak Termasuk ❌</label>
                            <textarea id="excludes" name="excludes" rows="6" placeholder="Satu item per baris:&#10;- Tiket pesawat&#10;- Tips guide&#10;- Pengeluaran pribadi"><?php echo esc_textarea($tour->excludes ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="ts-card">
                    <h3>Syarat & Ketentuan</h3>
                    <div class="ts-field">
                        <textarea id="terms" name="terms" rows="6" placeholder="Syarat dan ketentuan tour..."><?php echo esc_textarea($tour->terms ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="ts-form-sidebar">
                <div class="ts-card">
                    <h3>Pengaturan</h3>

                    <div class="ts-field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?php selected($tour->status ?? 'draft', 'draft'); ?>>Draft</option>
                            <option value="published" <?php selected($tour->status ?? '', 'published'); ?>>Published</option>
                            <option value="cancelled" <?php selected($tour->status ?? '', 'cancelled'); ?>>Dibatalkan</option>
                            <option value="completed" <?php selected($tour->status ?? '', 'completed'); ?>>Selesai</option>
                        </select>
                    </div>

                    <div class="ts-field">
                        <label for="price">Harga per Orang (Rp)</label>
                        <input type="number" id="price" name="price" min="0" step="1000"
                               value="<?php echo esc_attr($tour->price ?? 0); ?>">
                    </div>

                    <div class="ts-field">
                        <label for="max_participants">Kuota Peserta</label>
                        <input type="number" id="max_participants" name="max_participants" min="1"
                               value="<?php echo esc_attr($tour->max_participants ?? 20); ?>">
                    </div>
                </div>

                <div class="ts-card">
                    <h3>Jadwal</h3>
                    <div class="ts-field">
                        <label for="start_date">Tanggal Berangkat</label>
                        <input type="date" id="start_date" name="start_date"
                               value="<?php echo esc_attr($tour->start_date ?? ''); ?>">
                    </div>
                    <div class="ts-field">
                        <label for="end_date">Tanggal Pulang</label>
                        <input type="date" id="end_date" name="end_date"
                               value="<?php echo esc_attr($tour->end_date ?? ''); ?>">
                    </div>
                </div>

                <div class="ts-card">
                    <h3>Thumbnail</h3>
                    <div class="ts-field">
                        <input type="hidden" id="thumbnail_id" name="thumbnail_id"
                               value="<?php echo esc_attr($tour->thumbnail_id ?? 0); ?>">
                        <div id="ts-thumbnail-preview">
                            <?php if (!empty($tour->thumbnail_id)): ?>
                                <?php echo wp_get_attachment_image($tour->thumbnail_id, 'medium'); ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" id="ts-upload-thumbnail" class="button">
                            <?php echo !empty($tour->thumbnail_id) ? 'Ganti Gambar' : 'Pilih Gambar'; ?>
                        </button>
                        <?php if (!empty($tour->thumbnail_id)): ?>
                            <button type="button" id="ts-remove-thumbnail" class="button ts-btn-danger">Hapus</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ts-card">
                    <h3>Gallery</h3>
                    <div class="ts-field">
                        <input type="hidden" id="gallery_ids" name="gallery_ids"
                               value="<?php echo esc_attr($tour->gallery_ids ?? ''); ?>">
                        <div id="ts-gallery-preview" class="ts-gallery-grid">
                            <?php
                            $gallery = !empty($tour->gallery_ids) ? json_decode($tour->gallery_ids, true) : [];
                            if (is_array($gallery)):
                                foreach ($gallery as $gid):
                                    echo wp_get_attachment_image($gid, 'thumbnail');
                                endforeach;
                            endif;
                            ?>
                        </div>
                        <button type="button" id="ts-upload-gallery" class="button">Tambah Gambar</button>
                    </div>
                </div>

                <div class="ts-card" style="text-align:center;">
                    <button type="submit" class="button button-primary button-large" style="width:100%;">
                        💾 <?php echo $tour ? 'Simpan Perubahan' : 'Buat Tour'; ?>
                    </button>
                    <br><br>
                    <a href="<?php echo admin_url('admin.php?page=travelship-tours'); ?>" class="button">← Kembali</a>
                </div>
            </div>
        </div>
    </form>
</div>
