/**
 * TravelShip Admin JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // ─── Settings Tabs ─────────────────────
        $('.ts-tabs-nav .ts-tab').on('click', function(e) {
            e.preventDefault();
            var tabId = $(this).data('tab');
            
            $('.ts-tabs-nav .ts-tab').removeClass('active');
            $(this).addClass('active');
            
            $('.ts-tab-content').removeClass('active');
            $('#' + tabId).addClass('active');
        });

        // ─── Thumbnail Upload ──────────────────
        $('#ts-upload-thumbnail').on('click', function(e) {
            e.preventDefault();
            var frame = wp.media({
                title: 'Pilih Thumbnail Tour',
                button: { text: 'Gunakan Gambar Ini' },
                multiple: false
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#thumbnail_id').val(attachment.id);
                $('#ts-thumbnail-preview').html('<img src="' + attachment.sizes.medium.url + '" alt="">');
                $('#ts-upload-thumbnail').text('Ganti Gambar');
                
                if (!$('#ts-remove-thumbnail').length) {
                    $('<button type="button" id="ts-remove-thumbnail" class="button ts-btn-danger">Hapus</button>')
                        .insertAfter('#ts-upload-thumbnail');
                }
            });
            
            frame.open();
        });

        // Remove thumbnail
        $(document).on('click', '#ts-remove-thumbnail', function() {
            $('#thumbnail_id').val(0);
            $('#ts-thumbnail-preview').empty();
            $('#ts-upload-thumbnail').text('Pilih Gambar');
            $(this).remove();
        });

        // ─── Gallery Upload ────────────────────
        $('#ts-upload-gallery').on('click', function(e) {
            e.preventDefault();
            var frame = wp.media({
                title: 'Pilih Gambar Gallery',
                button: { text: 'Tambah ke Gallery' },
                multiple: true
            });
            
            frame.on('select', function() {
                var attachments = frame.state().get('selection').toJSON();
                var currentIds = $('#gallery_ids').val();
                var ids = currentIds ? JSON.parse(currentIds) : [];
                
                attachments.forEach(function(att) {
                    ids.push(att.id);
                    var thumbUrl = att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                    $('#ts-gallery-preview').append('<img src="' + thumbUrl + '" alt="">');
                });
                
                $('#gallery_ids').val(JSON.stringify(ids));
            });
            
            frame.open();
        });

    });
})(jQuery);
