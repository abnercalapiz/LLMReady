/**
 * LLM Ready Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Regenerate button handler
    $('#llmr-regenerate').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $spinner = $button.next('.spinner');
        var $message = $('#llmr-message');
        
        // Disable button and show spinner
        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        $message.empty();
        
        // Make AJAX request
        $.ajax({
            url: llmr_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'llmr_regenerate',
                nonce: llmr_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $message.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    
                    // Reload page after 2 seconds to show updated stats
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p>An error occurred. Please try again.</p></div>');
            },
            complete: function() {
                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
    
    // URL textarea management
    var $excludedUrls = $('textarea[name="llmr_settings[excluded_urls]"]');
    var $includedUrls = $('textarea[name="llmr_settings[included_urls]"]');
    
    // Auto-format URLs on blur
    function formatUrls($textarea) {
        var urls = $textarea.val().split('\n');
        var formatted = [];
        
        $.each(urls, function(i, url) {
            url = $.trim(url);
            if (url) {
                // Ensure URL has protocol
                if (!/^https?:\/\//i.test(url)) {
                    url = 'https://' + url;
                }
                formatted.push(url);
            }
        });
        
        $textarea.val(formatted.join('\n'));
    }
    
    $excludedUrls.on('blur', function() {
        formatUrls($(this));
    });
    
    $includedUrls.on('blur', function() {
        formatUrls($(this));
    });
    
    // Post type checkboxes - at least one must be selected
    var $postTypeCheckboxes = $('input[name="llmr_settings[post_types][]"]');
    
    $postTypeCheckboxes.on('change', function() {
        var checkedCount = $postTypeCheckboxes.filter(':checked').length;
        
        if (checkedCount === 0) {
            $(this).prop('checked', true);
            alert('At least one post type must be selected.');
        }
    });
    
    // Confirm before leaving page with unsaved changes
    var formChanged = false;
    
    $('form').on('change', 'input, textarea, select', function() {
        formChanged = true;
    });
    
    $('form').on('submit', function() {
        formChanged = false;
    });
    
    $(window).on('beforeunload', function(e) {
        if (formChanged) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
});