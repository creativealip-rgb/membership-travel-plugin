/**
 * Travel Membership Pro - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        initTravelMap();
        initTravelForm();
        initPhotoUpload();
        initTravelHistory();
    });
    
    /**
     * Initialize Travel Map
     */
    function initTravelMap() {
        var $mapContainer = $('#tmp-travel-map');
        if ($mapContainer.length === 0) return;
        
        // Get map settings from data attributes
        var lat = parseFloat($mapContainer.data('lat')) || -0.7893;
        var lng = parseFloat($mapContainer.data('lng')) || 113.9213;
        var zoom = parseInt($mapContainer.data('zoom')) || 3;
        
        // Create map
        var map = L.map('tmp-travel-map').setView([lat, lng], zoom);
        
        // Add tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);
        
        // Load user travels if logged in
        if (tmpAjax.isUserLoggedIn) {
            loadUserTravelsOnMap(map);
        }
        
        // Store map instance for later use
        $mapContainer.data('mapInstance', map);
    }
    
    /**
     * Load user travels on map
     */
    function loadUserTravelsOnMap(map) {
        $.ajax({
            url: tmpAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'tmp_get_user_travels',
                nonce: tmpAjax.userNonce
            },
            success: function(response) {
                if (response.success) {
                    var travels = response.data.travels;
                    
                    travels.forEach(function(travel) {
                        var dest = travel.destination;
                        var coords = dest.coordinates;
                        
                        if (coords && coords.lat && coords.lng) {
                            var marker = L.marker([coords.lat, coords.lng]).addTo(map);
                            
                            var popupContent = `
                                <div class="tmp-map-popup">
                                    <h4>${dest.title}</h4>
                                    <p>${dest.excerpt}</p>
                                    <p><strong>Visit Date:</strong> ${travel.travel.visit_date}</p>
                                    <a href="${dest.permalink}" class="tmp-btn tmp-btn-primary">View Details</a>
                                </div>
                            `;
                            
                            marker.bindPopup(popupContent);
                        }
                    });
                    
                    // Fit map to show all markers
                    if (travels.length > 0) {
                        var group = new L.featureGroup(map._layers);
                        map.fitBounds(group.getBounds(), {padding: [50, 50]});
                    }
                }
            }
        });
    }
    
    /**
     * Initialize Travel Form
     */
    function initTravelForm() {
        var $form = $('#tmp-add-travel-form');
        if ($form.length === 0) return;
        
        $form.on('submit', function(e) {
            e.preventDefault();
            
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = $submitBtn.text();
            $submitBtn.prop('disabled', true).text(tmpAjax.i18n.loading);
            
            // Collect form data
            var formData = {
                action: 'tmp_add_travel',
                nonce: tmpAjax.nonce,
                data: {
                    title: $form.find('#tmp-destination-title').val(),
                    description: $form.find('#tmp-destination-description').val(),
                    visit_date: $form.find('#tmp-visit-date').val(),
                    country_id: $form.find('#tmp-country').val(),
                    category_ids: $form.find('#tmp-category').val(),
                    location: $form.find('#tmp-location').val(),
                    notes: $form.find('#tmp-notes').val(),
                    rating: $form.find('#tmp-rating').val(),
                    photos: $form.find('.tmp-photo-preview img').map(function() {
                        return $(this).data('attachment-id');
                    }).get(),
                    coordinates: {
                        lat: $form.find('#tmp-lat').val(),
                        lng: $form.find('#tmp-lng').val()
                    }
                }
            };
            
            // Submit
            $.ajax({
                url: tmpAjax.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showNotice('success', tmpAjax.i18n.success + ' ' + response.data.message);
                        $form[0].reset();
                        $('.tmp-photo-preview').empty();
                        
                        // Refresh travel history
                        refreshTravelHistory();
                        
                        // Refresh stats
                        refreshStats();
                    } else {
                        showNotice('error', tmpAjax.i18n.error + ': ' + response.data.message);
                    }
                },
                error: function() {
                    showNotice('error', tmpAjax.i18n.error + ': Failed to add travel');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
    }
    
    /**
     * Initialize Photo Upload
     */
    function initPhotoUpload() {
        var $uploadArea = $('.tmp-photo-upload');
        if ($uploadArea.length === 0) return;
        
        var $fileInput = $uploadArea.find('input[type="file"]');
        var $preview = $uploadArea.siblings('.tmp-photo-preview');
        
        $uploadArea.on('click', function() {
            $fileInput.click();
        });
        
        $fileInput.on('change', function() {
            var files = this.files;
            if (files.length === 0) return;
            
            Array.from(files).forEach(function(file) {
                uploadPhoto(file, $preview);
            });
        });
        
        // Drag and drop
        $uploadArea.on('dragover dragenter', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });
        
        $uploadArea.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
        });
        
        $uploadArea.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            Array.from(files).forEach(function(file) {
                uploadPhoto(file, $preview);
            });
        });
    }
    
    /**
     * Upload photo
     */
    function uploadPhoto(file, $preview) {
        var formData = new FormData();
        formData.append('action', 'tmp_upload_photo');
        formData.append('nonce', tmpAjax.uploadNonce);
        formData.append('photo', file);
        
        $.ajax({
            url: tmpAjax.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    var img = $('<img>')
                        .attr('src', response.data.thumbnail)
                        .attr('data-attachment-id', response.data.attachment_id)
                        .css({
                            'width': '100px',
                            'height': '100px',
                            'object-fit': 'cover',
                            'border-radius': '4px'
                        });
                    
                    $preview.append(img);
                } else {
                    showNotice('error', tmpAjax.i18n.error + ': ' + response.data.message);
                }
            },
            error: function() {
                showNotice('error', tmpAjax.i18n.error + ': Upload failed');
            }
        });
    }
    
    /**
     * Initialize Travel History
     */
    function initTravelHistory() {
        $('.tmp-remove-travel').on('click', function() {
            if (!confirm(tmpAjax.i18n.confirmRemove)) return;
            
            var $btn = $(this);
            var destinationId = $btn.data('destination-id');
            
            $.ajax({
                url: tmpAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tmp_remove_travel',
                    nonce: tmpAjax.nonce,
                    destination_id: destinationId
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('.tmp-travel-card').fadeOut();
                        refreshStats();
                        showNotice('success', tmpAjax.i18n.success);
                    } else {
                        showNotice('error', response.data.message);
                    }
                }
            });
        });
    }
    
    /**
     * Refresh travel history
     */
    function refreshTravelHistory() {
        var $history = $('.tmp-travel-history');
        if ($history.length === 0) return;
        
        $.ajax({
            url: tmpAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'tmp_get_user_travels',
                nonce: tmpAjax.userNonce
            },
            success: function(response) {
                if (response.success) {
                    // Re-render history (simplified - in production would use template)
                    location.reload();
                }
            }
        });
    }
    
    /**
     * Refresh stats
     */
    function refreshStats() {
        var $stats = $('.tmp-stats-grid');
        if ($stats.length === 0) return;
        
        $.ajax({
            url: tmpAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'tmp_get_travel_stats',
                nonce: tmpAjax.userNonce
            },
            success: function(response) {
                if (response.success) {
                    // Update stat numbers
                    $stats.find('.tmp-stat-destinations .tmp-stat-number').text(response.data.total_destinations);
                    $stats.find('.tmp-stat-countries .tmp-stat-number').text(response.data.countries);
                    $stats.find('.tmp-stat-photos .tmp-stat-number').text(response.data.photos);
                }
            }
        });
    }
    
    /**
     * Show notice
     */
    function showNotice(type, message) {
        var $notice = $('<div class="tmp-notice tmp-notice-' + type + '">')
            .html('<p>' + message + '</p>')
            .css({
                'padding': '15px',
                'margin': '20px 0',
                'border-radius': '4px',
                'background': type === 'success' ? '#d4edda' : '#f8d7da',
                'border': '1px solid ' + (type === 'success' ? '#c3e6cb' : '#f5c6cb'),
                'color': type === 'success' ? '#155724' : '#721c24'
            });
        
        $('.tmp-dashboard').prepend($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
})(jQuery);
