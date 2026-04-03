/**
 * Travel Membership Pro - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initExportButton();
        initQuickActions();
    });
    
    /**
     * Initialize export button
     */
    function initExportButton() {
        $('.tmp-export-csv').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var originalText = $btn.text();
            $btn.prop('disabled', true).text('Exporting...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tmp_export_data',
                    nonce: tmpAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.download_url;
                    } else {
                        alert('Export failed: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Export failed. Please try again.');
                },
                complete: function() {
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        });
    }
    
    /**
     * Initialize quick actions
     */
    function initQuickActions() {
        // Confirm before deleting destination
        $('.submitdelete').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this destination? This will also remove it from all user travel histories.')) {
                e.preventDefault();
            }
        });
    }
    
})(jQuery);
