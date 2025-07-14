jQuery(document).ready(function($) {
    // Add Feature functionality
    $('#add-feature').click(function() {
        var index = $('#features-container .feature-item').length;
        var html = '<div class="feature-item" style="border: 1px solid #ddd; padding: 10px; margin: 5px 0;">' +
                   '<input type="text" name="features[' + index + '][title]" placeholder="Feature Title" value="" style="width: 30%; margin-right: 10px;" />' +
                   '<input type="text" name="features[' + index + '][icon]" placeholder="Icon" value="" style="width: 10%; margin-right: 10px;" />' +
                   '<input type="text" name="features[' + index + '][description]" placeholder="Description" value="" style="width: 50%;" />' +
                   '<button type="button" class="remove-feature" style="margin-left: 10px;">Remove</button>' +
                   '</div>';
        $('#features-container').append(html);
    });
    
    // Remove Feature functionality
    $(document).on('click', '.remove-feature', function() {
        $(this).closest('.feature-item').remove();
    });
    
    // Add Reward functionality
    $('#add-reward').click(function() {
        var index = $('#rewards-container .reward-item').length;
        var html = '<div class="reward-item" style="border: 1px solid #ddd; padding: 10px; margin: 5px 0;">' +
                   '<input type="text" name="rewards[' + index + '][category]" placeholder="Category" value="" style="width: 30%; margin-right: 10px;" />' +
                   '<input type="text" name="rewards[' + index + '][rate]" placeholder="Rate" value="" style="width: 20%; margin-right: 10px;" />' +
                   '<input type="text" name="rewards[' + index + '][description]" placeholder="Description" value="" style="width: 40%;" />' +
                   '<button type="button" class="remove-reward" style="margin-left: 10px;">Remove</button>' +
                   '</div>';
        $('#rewards-container').append(html);
    });
    
    // Remove Reward functionality
    $(document).on('click', '.remove-reward', function() {
        $(this).closest('.reward-item').remove();
    });
    
    // Add Fee functionality
    $('#add-fee').click(function() {
        var index = $('#fees-container .fee-item').length;
        var html = '<div class="fee-item" style="border: 1px solid #ddd; padding: 10px; margin: 5px 0;">' +
                   '<input type="text" name="fees[' + index + '][type]" placeholder="Fee Type" value="" style="width: 30%; margin-right: 10px;" />' +
                   '<input type="text" name="fees[' + index + '][amount]" placeholder="Amount" value="" style="width: 20%; margin-right: 10px;" />' +
                   '<input type="text" name="fees[' + index + '][description]" placeholder="Description" value="" style="width: 40%;" />' +
                   '<button type="button" class="remove-fee" style="margin-left: 10px;">Remove</button>' +
                   '</div>';
        $('#fees-container').append(html);
    });
    
    // Remove Fee functionality
    $(document).on('click', '.remove-fee', function() {
        $(this).closest('.fee-item').remove();
    });
    
    // Add Eligibility functionality
    $('#add-eligibility').click(function() {
        var index = $('#eligibility-container .eligibility-item').length;
        var html = '<div class="eligibility-item" style="border: 1px solid #ddd; padding: 10px; margin: 5px 0;">' +
                   '<input type="text" name="eligibility[' + index + '][criteria]" placeholder="Criteria" value="" style="width: 40%; margin-right: 10px;" />' +
                   '<input type="text" name="eligibility[' + index + '][value]" placeholder="Value" value="" style="width: 50%;" />' +
                   '<button type="button" class="remove-eligibility" style="margin-left: 10px;">Remove</button>' +
                   '</div>';
        $('#eligibility-container').append(html);
    });
    
    // Remove Eligibility functionality
    $(document).on('click', '.remove-eligibility', function() {
        $(this).closest('.eligibility-item').remove();
    });

    // Media uploader
    var mediaUploader;
    $('.upload_image_button').click(function(e) {
        e.preventDefault();
        var button = $(this);
        var inputField = button.prev();
        var previewWrapper = button.next();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            inputField.val(attachment.url);
            previewWrapper.html('<img src="' + attachment.url + '" style="max-width: 200px; margin-top: 10px;" />');
        });

        mediaUploader.open();
    });
});
