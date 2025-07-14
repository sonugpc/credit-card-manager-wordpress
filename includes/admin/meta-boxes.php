<?php
/**
 * Meta Boxes for Credit Card Post Type
 *
 * @package Credit Card Manager
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Add Meta Boxes
 */
function ccm_add_meta_boxes() {
    add_meta_box(
        'credit-card-details',
        __('Credit Card Details', 'credit-card-manager'),
        'ccm_meta_box_callback',
        'credit-card',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'ccm_add_meta_boxes');

/**
 * Meta Box Callback
 */
function ccm_meta_box_callback($post) {
    wp_nonce_field('credit_card_meta_box', 'credit_card_meta_box_nonce');
    
    // Get current values
    $card_image_url = get_post_meta($post->ID, 'card_image_url', true);
    $rating = get_post_meta($post->ID, 'rating', true);
    $review_count = get_post_meta($post->ID, 'review_count', true);
    $annual_fee = get_post_meta($post->ID, 'annual_fee', true);
    $joining_fee = get_post_meta($post->ID, 'joining_fee', true);
    $welcome_bonus = get_post_meta($post->ID, 'welcome_bonus', true);
    $welcome_bonus_points = get_post_meta($post->ID, 'welcome_bonus_points', true);
    $welcome_bonus_type = get_post_meta($post->ID, 'welcome_bonus_type', true);
    $cashback_rate = get_post_meta($post->ID, 'cashback_rate', true);
    $credit_limit = get_post_meta($post->ID, 'credit_limit', true);
    $interest_rate = get_post_meta($post->ID, 'interest_rate', true);
    $processing_time = get_post_meta($post->ID, 'processing_time', true);
    $min_income = get_post_meta($post->ID, 'min_income', true);
    $min_age = get_post_meta($post->ID, 'min_age', true);
    $max_age = get_post_meta($post->ID, 'max_age', true);
    $apply_link = get_post_meta($post->ID, 'apply_link', true);
    $featured = get_post_meta($post->ID, 'featured', true);
    $trending = get_post_meta($post->ID, 'trending', true);
    $theme_color = get_post_meta($post->ID, 'theme_color', true);
    
    // Get array fields
    $pros = get_post_meta($post->ID, 'pros', true) ?: array();
    $cons = get_post_meta($post->ID, 'cons', true) ?: array();
    $best_for = get_post_meta($post->ID, 'best_for', true) ?: array();
    $documents = get_post_meta($post->ID, 'documents', true) ?: array();
    
    ?>
    <div class="credit-card-meta-container">
        <style>
            .credit-card-meta-container { max-width: 100%; }
            .ccm-field-group { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; }
            .ccm-field-group h3 { margin-top: 0; color: #333; }
            .ccm-field { margin-bottom: 15px; }
            .ccm-field label { display: block; font-weight: bold; margin-bottom: 5px; }
            .ccm-field input, .ccm-field select, .ccm-field textarea { width: 100%; max-width: 400px; }
            .ccm-array-field { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; }
            .ccm-array-item { margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
            .ccm-array-item input { flex: 1; }
            .ccm-remove-item { background: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; }
            .ccm-add-item { background: #28a745; color: white; border: none; padding: 5px 15px; cursor: pointer; }
            .ccm-checkbox-group { display: flex; align-items: center; gap: 10px; }
            .ccm-image-upload { border: 1px solid #ddd; padding: 15px; background: white; }
        </style>
        
        <div class="ccm-field-group">
            <h3><?php _e('Basic Information', 'credit-card-manager'); ?></h3>
            
            <div class="ccm-field">
                <label><?php _e('Card Image', 'credit-card-manager'); ?></label>
                <div class="ccm-image-upload">
                    <input type="hidden" id="card_image_id" name="card_image_id" value="<?php echo esc_attr(get_post_meta($post->ID, 'card_image_id', true)); ?>" />
                    <input type="text" id="card_image_url" name="card_image_url" value="<?php echo esc_url($card_image_url); ?>" placeholder="<?php _e('Enter image URL or upload image', 'credit-card-manager'); ?>" />
                    <button type="button" class="button" id="upload_image_button"><?php _e('Upload Image', 'credit-card-manager'); ?></button>
                    <div id="image_preview" style="margin-top: 10px;">
                        <?php if ($card_image_url): ?>
                            <img src="<?php echo esc_url($card_image_url); ?>" style="max-width: 200px; height: auto;" />
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="ccm-field">
                <label for="rating"><?php _e('Rating (0-5)', 'credit-card-manager'); ?></label>
                <input type="number" id="rating" name="rating" value="<?php echo esc_attr($rating); ?>" min="0" max="5" step="0.1" />
            </div>
            
            <div class="ccm-field">
                <label for="review_count"><?php _e('Review Count', 'credit-card-manager'); ?></label>
                <input type="number" id="review_count" name="review_count" value="<?php echo esc_attr($review_count); ?>" min="0" />
            </div>
            
            <div class="ccm-checkbox-group">
                <input type="checkbox" id="featured" name="featured" value="1" <?php checked($featured, 1); ?> />
                <label for="featured"><?php _e('Featured Card', 'credit-card-manager'); ?></label>
            </div>
            
            <div class="ccm-checkbox-group">
                <input type="checkbox" id="trending" name="trending" value="1" <?php checked($trending, 1); ?> />
                <label for="trending"><?php _e('Trending Card', 'credit-card-manager'); ?></label>
            </div>
            
            <div class="ccm-field">
                <label for="theme_color"><?php _e('Theme Color', 'credit-card-manager'); ?></label>
                <input type="color" id="theme_color" name="theme_color" value="<?php echo esc_attr($theme_color ?: '#1e40af'); ?>" />
            </div>
        </div>
        
        <div class="ccm-field-group">
            <h3><?php _e('Fees & Benefits', 'credit-card-manager'); ?></h3>
            
            <div class="ccm-field">
                <label for="annual_fee"><?php _e('Annual Fee', 'credit-card-manager'); ?></label>
                <input type="text" id="annual_fee" name="annual_fee" value="<?php echo esc_attr($annual_fee); ?>" placeholder="₹2,500" />
            </div>
            
            <div class="ccm-field">
                <label for="joining_fee"><?php _e('Joining Fee', 'credit-card-manager'); ?></label>
                <input type="text" id="joining_fee" name="joining_fee" value="<?php echo esc_attr($joining_fee); ?>" placeholder="₹2,500" />
            </div>
            
            <div class="ccm-field">
                <label for="welcome_bonus"><?php _e('Welcome Bonus Description', 'credit-card-manager'); ?></label>
                <input type="text" id="welcome_bonus" name="welcome_bonus" value="<?php echo esc_attr($welcome_bonus); ?>" placeholder="10,000 reward points worth ₹2,500" />
            </div>
            
            <div class="ccm-field">
                <label for="welcome_bonus_points"><?php _e('Welcome Bonus Points/Amount', 'credit-card-manager'); ?></label>
                <input type="number" id="welcome_bonus_points" name="welcome_bonus_points" value="<?php echo esc_attr($welcome_bonus_points); ?>" min="0" />
            </div>
            
            <div class="ccm-field">
                <label for="welcome_bonus_type"><?php _e('Welcome Bonus Type', 'credit-card-manager'); ?></label>
                <select id="welcome_bonus_type" name="welcome_bonus_type">
                    <option value="points" <?php selected($welcome_bonus_type, 'points'); ?>><?php _e('Points', 'credit-card-manager'); ?></option>
                    <option value="money" <?php selected($welcome_bonus_type, 'money'); ?>><?php _e('Money', 'credit-card-manager'); ?></option>
                    <option value="cashback" <?php selected($welcome_bonus_type, 'cashback'); ?>><?php _e('Cashback', 'credit-card-manager'); ?></option>
                </select>
            </div>
            
            <div class="ccm-field">
                <label for="cashback_rate"><?php _e('Cashback/Reward Rate', 'credit-card-manager'); ?></label>
                <input type="text" id="cashback_rate" name="cashback_rate" value="<?php echo esc_attr($cashback_rate); ?>" placeholder="Up to 4% reward rate" />
            </div>
        </div>
        
        <div class="ccm-field-group">
            <h3><?php _e('Eligibility & Terms', 'credit-card-manager'); ?></h3>
            
            <div class="ccm-field">
                <label for="credit_limit"><?php _e('Credit Limit', 'credit-card-manager'); ?></label>
                <input type="text" id="credit_limit" name="credit_limit" value="<?php echo esc_attr($credit_limit); ?>" placeholder="Up to ₹10,00,000" />
            </div>
            
            <div class="ccm-field">
                <label for="interest_rate"><?php _e('Interest Rate', 'credit-card-manager'); ?></label>
                <input type="text" id="interest_rate" name="interest_rate" value="<?php echo esc_attr($interest_rate); ?>" placeholder="3.49% per month" />
            </div>
            
            <div class="ccm-field">
                <label for="processing_time"><?php _e('Processing Time', 'credit-card-manager'); ?></label>
                <input type="text" id="processing_time" name="processing_time" value="<?php echo esc_attr($processing_time); ?>" placeholder="7-10 working days" />
            </div>
            
            <div class="ccm-field">
                <label for="min_income"><?php _e('Minimum Income', 'credit-card-manager'); ?></label>
                <input type="text" id="min_income" name="min_income" value="<?php echo esc_attr($min_income); ?>" placeholder="₹6,00,000 annually" />
            </div>
            
            <div class="ccm-field">
                <label for="min_age"><?php _e('Minimum Age', 'credit-card-manager'); ?></label>
                <input type="text" id="min_age" name="min_age" value="<?php echo esc_attr($min_age); ?>" placeholder="21 years" />
            </div>
            
            <div class="ccm-field">
                <label for="max_age"><?php _e('Maximum Age', 'credit-card-manager'); ?></label>
                <input type="text" id="max_age" name="max_age" value="<?php echo esc_attr($max_age); ?>" placeholder="65 years" />
            </div>
            
            <div class="ccm-field">
                <label for="apply_link"><?php _e('Application Link', 'credit-card-manager'); ?></label>
                <input type="url" id="apply_link" name="apply_link" value="<?php echo esc_url($apply_link); ?>" placeholder="https://www.bank.com/apply" />
            </div>
        </div>
        
        <div class="ccm-field-group">
            <h3><?php _e('Pros', 'credit-card-manager'); ?></h3>
                <div class="ccm-array-field" id="pros-field">
               <?php if (!empty($pros)): ?>
                   <?php foreach ($pros as $index => $pro): ?>
                       <div class="ccm-array-item">
                           <input type="text" name="pros[]" value="<?php echo esc_attr($pro); ?>" placeholder="Enter a pro" />
                           <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                       </div>
                   <?php endforeach; ?>
               <?php else: ?>
                   <div class="ccm-array-item">
                       <input type="text" name="pros[]" value="" placeholder="Enter a pro" />
                       <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                   </div>
               <?php endif; ?>
               <button type="button" class="ccm-add-item" onclick="addArrayItem('pros-field', 'pros[]', 'Enter a pro')"><?php _e('Add Pro', 'credit-card-manager'); ?></button>
           </div>
       </div>
       
       <div class="ccm-field-group">
           <h3><?php _e('Cons', 'credit-card-manager'); ?></h3>
           <div class="ccm-array-field" id="cons-field">
               <?php if (!empty($cons)): ?>
                   <?php foreach ($cons as $index => $con): ?>
                       <div class="ccm-array-item">
                           <input type="text" name="cons[]" value="<?php echo esc_attr($con); ?>" placeholder="Enter a con" />
                           <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                       </div>
                   <?php endforeach; ?>
               <?php else: ?>
                   <div class="ccm-array-item">
                       <input type="text" name="cons[]" value="" placeholder="Enter a con" />
                       <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                   </div>
               <?php endif; ?>
               <button type="button" class="ccm-add-item" onclick="addArrayItem('cons-field', 'cons[]', 'Enter a con')"><?php _e('Add Con', 'credit-card-manager'); ?></button>
           </div>
       </div>
       
       <div class="ccm-field-group">
           <h3><?php _e('Best For', 'credit-card-manager'); ?></h3>
           <div class="ccm-array-field" id="best-for-field">
               <?php if (!empty($best_for)): ?>
                   <?php foreach ($best_for as $index => $item): ?>
                       <div class="ccm-array-item">
                           <input type="text" name="best_for[]" value="<?php echo esc_attr($item); ?>" placeholder="Enter target audience" />
                           <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                       </div>
                   <?php endforeach; ?>
               <?php else: ?>
                   <div class="ccm-array-item">
                       <input type="text" name="best_for[]" value="" placeholder="Enter target audience" />
                       <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                   </div>
               <?php endif; ?>
               <button type="button" class="ccm-add-item" onclick="addArrayItem('best-for-field', 'best_for[]', 'Enter target audience')"><?php _e('Add Item', 'credit-card-manager'); ?></button>
           </div>
       </div>
       
       <div class="ccm-field-group">
           <h3><?php _e('Required Documents', 'credit-card-manager'); ?></h3>
           <div class="ccm-array-field" id="documents-field">
               <?php if (!empty($documents)): ?>
                   <?php foreach ($documents as $index => $document): ?>
                       <div class="ccm-array-item">
                           <input type="text" name="documents[]" value="<?php echo esc_attr($document); ?>" placeholder="Enter required document" />
                           <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                       </div>
                   <?php endforeach; ?>
               <?php else: ?>
                   <div class="ccm-array-item">
                       <input type="text" name="documents[]" value="" placeholder="Enter required document" />
                       <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
                   </div>
               <?php endif; ?>
               <button type="button" class="ccm-add-item" onclick="addArrayItem('documents-field', 'documents[]', 'Enter required document')"><?php _e('Add Document', 'credit-card-manager'); ?></button>
           </div>
       </div>
   </div>
   
   <script>
   function addArrayItem(containerId, inputName, placeholder) {
       const container = document.getElementById(containerId);
       const newItem = document.createElement('div');
       newItem.className = 'ccm-array-item';
       newItem.innerHTML = `
           <input type="text" name="${inputName}" value="" placeholder="${placeholder}" />
           <button type="button" class="ccm-remove-item" onclick="removeArrayItem(this)"><?php _e('Remove', 'credit-card-manager'); ?></button>
       `;
       container.insertBefore(newItem, container.lastElementChild);
   }
   
   function removeArrayItem(button) {
       const item = button.parentElement;
       const container = item.parentElement;
       if (container.querySelectorAll('.ccm-array-item').length > 1) {
           item.remove();
       }
   }
   </script>
   <?php
}

/**
 * Save Meta Data
 */
function ccm_save_meta_data($post_id) {
    // Verify nonce
    if (!isset($_POST['credit_card_meta_box_nonce']) || 
        !wp_verify_nonce($_POST['credit_card_meta_box_nonce'], 'credit_card_meta_box')) {
        return;
    }
    
    // Check if user has permission
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Don't save on autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Only save for credit-card post type
    if (get_post_type($post_id) !== 'credit-card') {
        return;
    }
    
    // Save simple fields
    $simple_fields = array(
        'card_image_url', 'rating', 'review_count', 'annual_fee', 'joining_fee',
        'welcome_bonus', 'welcome_bonus_points', 'welcome_bonus_type', 'cashback_rate',
        'credit_limit', 'interest_rate', 'processing_time', 'min_income',
        'min_age', 'max_age', 'apply_link', 'theme_color'
    );
    
    foreach ($simple_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    // Save boolean fields
    update_post_meta($post_id, 'featured', isset($_POST['featured']) ? 1 : 0);
    update_post_meta($post_id, 'trending', isset($_POST['trending']) ? 1 : 0);
    
    // Save array fields
    $array_fields = array('pros', 'cons', 'best_for', 'documents');
    foreach ($array_fields as $field) {
        if (isset($_POST[$field]) && is_array($_POST[$field])) {
            $clean_array = array_filter(array_map('sanitize_text_field', $_POST[$field]));
            update_post_meta($post_id, $field, $clean_array);
        }
    }
    
    // Save numeric values for better filtering
    if (isset($_POST['annual_fee'])) {
        $annual_fee_numeric = ccm_extract_numeric_value($_POST['annual_fee']);
        update_post_meta($post_id, 'annual_fee_numeric', $annual_fee_numeric);
    }
    
    if (isset($_POST['min_income'])) {
        $min_income_numeric = ccm_extract_numeric_value($_POST['min_income']);
        update_post_meta($post_id, 'min_income_numeric', $min_income_numeric);
    }
}
add_action('save_post', 'ccm_save_meta_data');
