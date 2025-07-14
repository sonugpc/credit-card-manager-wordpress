javascriptjQuery(document).ready(function($) {
    
    // Media uploader for card image
    let mediaUploader;
    
    $('#upload_image_button').on('click', function(e) {
        e.preventDefault();
        
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Credit Card Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#card_image_id').val(attachment.id);
            $('#card_image_url').val(attachment.url);
            $('#image_preview').html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto;" />');
        });
        
        mediaUploader.open();
    });
    
    // URL input change handler
    $('#card_image_url').on('change', function() {
        const url = $(this).val();
        if (url) {
            $('#image_preview').html('<img src="' + url + '" style="max-width: 200px; height: auto;" />');
        } else {
            $('#image_preview').html('');
        }
    });
    
    // Dynamic array field management
    window.addArrayItem = function(containerId, inputName, placeholder) {
        const container = $('#' + containerId);
        const newItem = $('<div class="ccm-array-item">' +
            '<input type="text" name="' + inputName + '" value="" placeholder="' + placeholder + '" />' +
            '<button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)">Remove</button>' +
            '</div>');
        
        container.find('.ccm-add-item').before(newItem);
    };
    
    window.removeArrayItem = function(button) {
        const item = $(button).parent();
        const container = item.parent();
        
        // Don't remove if it's the last item
        if (container.find('.ccm-array-item').length > 1) {
            item.remove();
        } else {
            // Clear the input instead
            item.find('input').val('');
        }
    };
    
    // Color picker enhancement
    if (typeof $.wp === 'object' && typeof $.wp.wpColorPicker === 'function') {
        $('#theme_color').wpColorPicker();
    }
    
    // Form validation
    $('form#post').on('submit', function(e) {
        let hasErrors = false;
        const errors = [];
        
        // Validate rating
        const rating = parseFloat($('#rating').val());
        if (rating && (rating < 0 || rating > 5)) {
            errors.push('Rating must be between 0 and 5');
            hasErrors = true;
        }
        
        // Validate review count
        const reviewCount = parseInt($('#review_count').val());
        if (reviewCount && reviewCount < 0) {
            errors.push('Review count cannot be negative');
            hasErrors = true;
        }
        
        // Validate URLs
        const imageUrl = $('#card_image_url').val();
        const applyLink = $('#apply_link').val();
        
        if (imageUrl && !isValidUrl(imageUrl)) {
            errors.push('Card image URL is not valid');
            hasErrors = true;
        }
        
        if (applyLink && !isValidUrl(applyLink)) {
            errors.push('Application link is not valid');
            hasErrors = true;
        }
        
        if (hasErrors) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
        }
    });
    
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // Auto-save functionality
    let autoSaveTimeout;
    $('.ccm-field input, .ccm-field select, .ccm-field textarea').on('input change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            // Trigger WordPress autosave
            if (typeof wp !== 'undefined' && wp.autosave) {
                wp.autosave.server.triggerSave();
            }
        }, 2000);
    });
});
