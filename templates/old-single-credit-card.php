<?php
/**
 * The template for displaying a single "Credit Card" post type.
 * Inspired by a React component design for a feature-rich layout.
 *
 * @package YourThemeName
 */

get_header();

// --- Data Fetching & Preparation ---
$post_id = get_the_ID();
$meta = get_post_meta($post_id);
$card_name = get_the_title();

// Helper function to safely get meta values
function get_cc_meta($key, $default = '', $is_numeric = false, $unserialize = false) {
    global $meta;
    $value = isset($meta[$key][0]) ? $meta[$key][0] : $default;
    if ($unserialize) {
        return maybe_unserialize($value) ?: $default;
    }
    return $is_numeric ? (is_numeric($value) ? $value : $default) : $value;
}

// --- Assign all variables ---
$rating = get_cc_meta('rating', 0, true);
$review_count = get_cc_meta('review_count', 0, true);
$annual_fee = get_cc_meta('annual_fee', 'N/A');
$joining_fee = get_cc_meta('joining_fee', 'N/A');
$welcome_bonus = get_cc_meta('welcome_bonus', 'N/A');
$credit_limit = get_cc_meta('credit_limit', 'N/A');
$processing_time = get_cc_meta('processing_time', 'N/A');
$min_income = get_cc_meta('min_income', 'N/A');
$apply_link = esc_url(get_cc_meta('apply_link', '#'));
$trending = (bool) get_cc_meta('trending', false);

// Design & Color
$gradient_class = get_cc_meta('gradient', 'from-gray-700 to-gray-900'); // Default gradient
$theme_color = get_cc_meta('theme_color', '#1e40af');

// Scores
$overall_score = get_cc_meta('overall_score', 0, true);
$reward_score = get_cc_meta('reward_score', 0, true);
$fees_score = get_cc_meta('fees_score', 0, true);
$benefits_score = get_cc_meta('benefits_score', 0, true);
$support_score = get_cc_meta('support_score', 0, true);

// Array data
$pros = get_cc_meta('pros', [], false, true);
$cons = get_cc_meta('cons', [], false, true);
$best_for = get_cc_meta('best_for', [], false, true);
$features = get_cc_meta('features', [], false, true);
$rewards = get_cc_meta('rewards', [], false, true);
$fees = get_cc_meta('fees', [], false, true);
$eligibility = get_cc_meta('eligibility', [], false, true);
$documents = get_cc_meta('documents', [], false, true);

// Get Network Type taxonomy
$network_terms = get_the_terms($post_id, 'network-type');
$network_type = !is_wp_error($network_terms) && !empty($network_terms) ? $network_terms[0]->name : 'N/A';

// --- SVG Icons ---
function get_cc_icon($name, $classes = '') {
    $icons = [
        'star' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z"/></svg>',
        'zap' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
        'credit-card' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>',
        'gift' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>',
        'calculator' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="18"></line><line x1="12" y1="14" x2="12" y2="18"></line><line x1="8" y1="14" x2="8" y2="18"></line><line x1="12" y1="10" x2="12" y2="10"></line></svg>',
        'overview' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>',
        'features' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 12.5l3-3 3 3 3-3 3 3 3-3"></path><path d="M4.5 18.5l3-3 3 3 3-3 3 3 3-3"></path></svg>',
        'rewards' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 17 17 23 15.79 13.88"></polyline></svg>',
        'fees' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
        'eligibility' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>',
        'check-circle' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
        'x-circle' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
        'target' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>',
        'wallet' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"></path><path d="M20 12H8v-2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2z"></path></svg>',
        'clock' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
        'trending-up' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>',
        'file-text' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
        'arrow-right' => '<svg class="'.$classes.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
    ];
    return isset($icons[$name]) ? $icons[$name] : '';
}
?>

<style>
    /* Tailwind-inspired CSS for the template */
    :root {
        --cc-theme-color: <?php echo esc_attr($theme_color); ?>;
        --cc-gray-50: #f9fafb; --cc-gray-100: #f3f4f6; --cc-gray-200: #e5e7eb; --cc-gray-600: #4b5563; --cc-gray-700: #374151; --cc-gray-800: #1f2937;
        --cc-blue-50: #eff6ff; --cc-blue-200: #bfdbfe; --cc-blue-500: #3b82f6; --cc-blue-600: #2563eb; --cc-blue-700: #1d4ed8;
        --cc-green-500: #22c55e; --cc-red-500: #ef4444; --cc-red-600: #dc2626; --cc-red-700: #b91c1c;
        --cc-purple-600: #9333ea;
        --cc-yellow-400: #facc15;
    }
    .theme-background {
        background: var(--cc-theme-color);
    }
    .cc-body { background-color: var(--cc-gray-50); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    .cc-hero { color: white; position: relative; overflow: hidden; }
    .cc-hero.gradient-1 { background-image: linear-gradient(to bottom right, #1e40af, #7c3aed); }
    .cc-hero.gradient-2 { background-image: linear-gradient(to bottom right, #be185d, #f472b6); }
    .cc-hero.gradient-3 { background-image: linear-gradient(to bottom right, #047857, #34d399); }
    .cc-hero.gradient-default { background-image: linear-gradient(to bottom right, #4b5563, #1f2937); }
    .cc-hero-content { position: relative; z-index: 10; padding: 2rem 1.5rem; }
    .cc-hero-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .cc-hero-img-wrap { width: 100px; height: 63px; }
    .cc-hero-img-wrap img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .cc-hero-title h1 { font-size: 1.25rem; font-weight: 700; margin: 0 0 4px; }
    .cc-hero-title p { opacity: 0.9; font-size: 0.875rem; margin: 0; }
    .cc-rating-trending { display: flex; align-items: center; gap: 0.75rem; margin-top: 0.5rem; }
    .cc-rating { display: flex; align-items: center; gap: 0.25rem; }
    .cc-rating .icon { width: 1rem; height: 1rem; color: var(--cc-yellow-400); }
    .cc-rating span { font-size: 0.875rem; font-weight: 500; }
    .cc-trending-badge { background-color: var(--cc-red-500); font-size: 0.75rem; padding: 4px 8px; border-radius: 99px; font-weight: 500; display: flex; align-items: center; gap: 4px; }
    .cc-trending-badge .icon { width: 0.75rem; height: 0.75rem; }
    .cc-hero-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
    .cc-stat-box { background-color: rgba(255,255,255,0.1); backdrop-filter: blur(4px); border-radius: 12px; padding: 1rem; border: 1px solid rgba(255,255,255,0.2); }
    .cc-stat-box .label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; opacity: 0.8; margin-bottom: 0.5rem; }
    .cc-stat-box .label .icon { width: 1rem; height: 1rem; }
    .cc-stat-box .value { font-size: 1.125rem; font-weight: 700; }
    .cc-score-breakdown { background-color: rgba(255,255,255,0.1); backdrop-filter: blur(4px); border-radius: 12px; padding: 1rem; border: 1px solid rgba(255,255,255,0.2); }
    .cc-score-breakdown .overall { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
    .cc-score-breakdown .overall .label { font-size: 0.875rem; opacity: 0.8; }
    .cc-score-breakdown .overall .value { font-size: 1.5rem; font-weight: 700; }
    .cc-score-breakdown .details { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.875rem; }
    .cc-score-breakdown .details > div { display: flex; justify-content: space-between; }
    .cc-score-breakdown .details .label { opacity: 0.7; }
    .cc-score-breakdown .details .value { font-weight: 500; }
    .cc-actions-bar { padding: 1rem 1.5rem; margin-top: -1rem; position: relative; z-index: 10; }
    .cc-actions-bar-inner { background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid var(--cc-gray-100); padding: 1rem; display: flex; gap: 0.75rem; }
    .cc-btn { flex: 1; padding: 0.75rem; border-radius: 12px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border: none; cursor: pointer; transition: all 0.2s; }
    .cc-btn .icon { width: 1.25rem; height: 1.25rem; }
    .cc-btn-primary { background-image: linear-gradient(to right, var(--cc-blue-600), var(--cc-purple-600)); color: white; }
    .cc-btn-primary:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    .cc-btn-secondary { background-color: var(--cc-gray-100); color: var(--cc-gray-700); }
    .cc-btn-secondary:hover { background-color: var(--cc-gray-200); }
    .cc-tabs-nav { padding: 1rem 1.5rem; }
    .cc-tabs-nav-inner { background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid var(--cc-gray-100); display: flex; overflow-x: auto; }
    .cc-tab-link { flex: 1; min-width: 0; padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.25rem; border: none; background: transparent; cursor: pointer; color: var(--cc-gray-600); border-bottom: 2px solid transparent; }
    .cc-tab-link .icon { width: 1rem; height: 1rem; }
    .cc-tab-link:hover { background-color: var(--cc-gray-50); }
    .cc-tab-link.active { color: var(--cc-blue-600); border-color: var(--cc-blue-600); background-color: var(--cc-blue-50); }
    .cc-content-wrap { padding: 0 1.5rem 1.5rem; }
    .cc-tab-content { display: none; animation: fadeIn 0.5s; }
    .cc-tab-content.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .cc-section { background: white; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid var(--cc-gray-100); padding: 1.5rem; margin-bottom: 1.5rem; }
    .cc-section-title { font-size: 1.125rem; font-weight: 700; color: var(--cc-gray-800); margin: 0 0 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .cc-section-title .icon { width: 1.25rem; height: 1.25rem; }
    .cc-overview-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    .cc-overview-item { text-align: center; padding: 1rem; background-color: var(--cc-gray-50); border-radius: 12px; }
    .cc-overview-item .icon { width: 2rem; height: 2rem; margin: 0 auto 0.5rem; color: var(--cc-blue-500); }
    .cc-overview-item .label { font-size: 0.875rem; color: var(--cc-gray-600); }
    .cc-overview-item .value { font-weight: 500; color: var(--cc-gray-800); }
    .cc-pros-cons-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
    @media (min-width: 768px) { .cc-pros-cons-grid { grid-template-columns: 1fr 1fr; } }
    .cc-list ul { list-style: none; padding: 0; margin: 0; }
    .cc-list li { display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.75rem; }
    .cc-list .icon { width: 1rem; height: 1rem; flex-shrink: 0; margin-top: 2px; }
    .cc-list .icon.pro { color: var(--cc-green-500); }
    .cc-list .icon.con { color: var(--cc-red-600); }
    .cc-list span { font-size: 0.875rem; color: var(--cc-gray-700); }
    .cc-best-for-tags { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .cc-best-for-tags span { background-color: var(--cc-blue-50); color: var(--cc-blue-700); font-size: 0.875rem; padding: 0.5rem 0.75rem; border-radius: 99px; border: 1px solid var(--cc-blue-200); }
    .cc-feature-item { display: flex; align-items: flex-start; gap: 1rem; padding: 1rem; border-radius: 12px; transition: background-color 0.2s; }
    .cc-feature-item:not(:last-child) { border-bottom: 1px solid var(--cc-gray-100); }
    .cc-feature-item:hover { background-color: var(--cc-gray-50); }
    .cc-feature-item .icon { width: 2rem; height: 2rem; color: var(--cc-blue-500); }
    .cc-feature-item .title { font-weight: 700; margin-bottom: 0.25rem; color: var(--cc-gray-800); }
    .cc-feature-item .desc { font-size: 0.875rem; color: var(--cc-gray-600); }
    .cc-data-table .item { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--cc-gray-100); }
    .cc-data-table .item:last-child { border-bottom: none; }
    .cc-data-table .label { color: var(--cc-gray-700); }
    .cc-data-table .value { font-weight: 500; color: var(--cc-gray-800); }
    .cc-data-table .value.positive { color: var(--cc-green-500); }
    .cc-data-table .value.negative { color: var(--cc-red-600); }
    .cc-bottom-cta { background-image: linear-gradient(to right, var(--cc-blue-600), var(--cc-purple-600)); border-radius: 16px; padding: 1.5rem; text-align: center; color: white; }
    .cc-bottom-cta h3 { font-size: 1.25rem; font-weight: 700; margin: 0 0 0.5rem; }
    .cc-bottom-cta p { opacity: 0.8; font-size: 0.875rem; margin: 0 0 1rem; }
    .cc-bottom-cta .cc-btn { background: white; color: var(--cc-blue-600); display: inline-flex; width: auto; padding: 0.75rem 2rem; }
</style>

<div class="cc-body">
    <main id="primary" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            
            <!-- HERO SECTION -->
            <div class="cc-hero theme-background <?php echo esc_attr($gradient_class); ?>">
                <div class="cc-hero-content">
                    <div class="cc-hero-header">
                        <div class="cc-hero-img-wrap">
                            <?php if (has_post_thumbnail()) : ?>
                                <img src="<?php echo get_the_post_thumbnail_url($post_id, 'medium'); ?>" alt="<?php echo esc_attr($card_name); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="cc-hero-title">
                            <h1><?php echo esc_html(get_bloginfo('name')); // Bank Name or Site Name ?></h1>
                            <p><?php echo esc_html($card_name); ?></p>
                            <div class="cc-rating-trending">
                                <div class="cc-rating">
                                    <?php echo get_cc_icon('star', 'icon'); ?>
                                    <span><?php echo esc_html($rating); ?> (<?php echo esc_html($review_count); ?> reviews)</span>
                                </div>
                                <?php if ($trending) : ?>
                                <div class="cc-trending-badge">
                                    <?php echo get_cc_icon('zap', 'icon'); ?>
                                    <span>TRENDING</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="cc-hero-stats">
                        <div class="cc-stat-box">
                            <div class="label"><?php echo get_cc_icon('credit-card', 'icon'); ?><span>Annual Fee</span></div>
                            <p class="value"><?php echo esc_html($annual_fee); ?></p>
                        </div>
                        <div class="cc-stat-box">
                            <div class="label"><?php echo get_cc_icon('gift', 'icon'); ?><span>Welcome Bonus</span></div>
                            <p class="value"><?php echo esc_html($welcome_bonus); ?></p>
                        </div>
                    </div>

                    <div class="cc-score-breakdown">
                        <div class="overall">
                            <span class="label">Overall Score</span>
                            <span class="value"><?php echo esc_html($overall_score); ?>/5</span>
                        </div>
                        <div class="details">
                            <div><span class="label">Rewards</span><span class="value"><?php echo esc_html($reward_score); ?>/5</span></div>
                            <div><span class="label">Benefits</span><span class="value"><?php echo esc_html($benefits_score); ?>/5</span></div>
                            <div><span class="label">Fees</span><span class="value"><?php echo esc_html($fees_score); ?>/5</span></div>
                            <div><span class="label">Support</span><span class="value"><?php echo esc_html($support_score); ?>/5</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUICK ACTIONS BAR -->
            <div class="cc-actions-bar">
                <div class="cc-actions-bar-inner">
                    <a href="<?php echo $apply_link; ?>" target="_blank" rel="noopener sponsored" class="cc-btn cc-btn-primary">
                        <?php echo get_cc_icon('zap', 'icon'); ?>
                        <span>Apply Now</span>
                    </a>
                    <button class="cc-btn cc-btn-secondary" onclick="alert('Calculator feature coming soon!');">
                        <?php echo get_cc_icon('calculator', 'icon'); ?>
                        <span>Calculator</span>
                    </button>
                </div>
            </div>

            <!-- TABS NAVIGATION -->
            <div class="cc-tabs-nav">
                <div class="cc-tabs-nav-inner">
                    <button class="cc-tab-link active" data-tab="overview"><?php echo get_cc_icon('overview', 'icon'); ?><span>Overview</span></button>
                    <button class="cc-tab-link" data-tab="features"><?php echo get_cc_icon('features', 'icon'); ?><span>Features</span></button>
                    <button class="cc-tab-link" data-tab="rewards"><?php echo get_cc_icon('rewards', 'icon'); ?><span>Rewards</span></button>
                    <button class="cc-tab-link" data-tab="fees"><?php echo get_cc_icon('fees', 'icon'); ?><span>Fees</span></button>
                    <button class="cc-tab-link" data-tab="eligibility"><?php echo get_cc_icon('eligibility', 'icon'); ?><span>Eligibility</span></button>
                </div>
            </div>

            <!-- TABS CONTENT -->
            <div class="cc-content-wrap">
                <!-- Overview Tab -->
                <div id="overview" class="cc-tab-content active">
                    <div class="cc-section">
                        <h3 class="cc-section-title">Quick Overview</h3>
                        <div class="cc-overview-grid">
                            <div class="cc-overview-item"><?php echo get_cc_icon('credit-card', 'icon'); ?><p class="label">Network</p><p class="value"><?php echo esc_html($network_type); ?></p></div>
                            <div class="cc-overview-item"><?php echo get_cc_icon('wallet', 'icon'); ?><p class="label">Credit Limit</p><p class="value"><?php echo esc_html($credit_limit); ?></p></div>
                            <div class="cc-overview-item"><?php echo get_cc_icon('clock', 'icon'); ?><p class="label">Processing Time</p><p class="value"><?php echo esc_html($processing_time); ?></p></div>
                            <div class="cc-overview-item"><?php echo get_cc_icon('trending-up', 'icon'); ?><p class="label">Min Income</p><p class="value"><?php echo esc_html($min_income); ?></p></div>
                        </div>
                    </div>
                    <div class="cc-pros-cons-grid">
                        <?php if (!empty($pros)): ?>
                        <div class="cc-section cc-list">
                            <h4 class="cc-section-title" style="color: var(--cc-green-500);"><?php echo get_cc_icon('check-circle', 'icon'); ?><span>Pros</span></h4>
                            <ul><?php foreach ($pros as $pro) echo '<li><span class="icon pro">' . get_cc_icon('check-circle') . '</span><span>' . esc_html($pro) . '</span></li>'; ?></ul>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($cons)): ?>
                        <div class="cc-section cc-list">
                            <h4 class="cc-section-title" style="color: var(--cc-red-700);"><?php echo get_cc_icon('x-circle', 'icon'); ?><span>Cons</span></h4>
                            <ul><?php foreach ($cons as $con) echo '<li><span class="icon con">' . get_cc_icon('x-circle') . '</span><span>' . esc_html($con) . '</span></li>'; ?></ul>
                        </div>
                        <?php endif; ?>
                    </div>
                     <?php if (!empty($best_for)): ?>
                    <div class="cc-section">
                        <h3 class="cc-section-title" style="color: var(--cc-blue-700);"><?php echo get_cc_icon('target', 'icon'); ?><span>Best For</span></h3>
                        <div class="cc-best-for-tags"><?php foreach ($best_for as $item) echo '<span>' . esc_html($item) . '</span>'; ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Features Tab -->
                <div id="features" class="cc-tab-content">
                    <div class="cc-section">
                        <h3 class="cc-section-title">Key Features</h3>
                        <?php if (!empty($features)): foreach ($features as $feature): ?>
                        <div class="cc-feature-item">
                            <?php echo get_cc_icon('gift', 'icon'); // Generic icon, can be dynamic ?>
                            <div>
                                <p class="title"><?php echo esc_html($feature['title']); ?></p>
                                <p class="desc"><?php echo esc_html($feature['description']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; else: echo '<p>No specific features listed.</p>'; endif; ?>
                    </div>
                </div>

                <!-- Rewards Tab -->
                <div id="rewards" class="cc-tab-content">
                    <div class="cc-section cc-data-table">
                        <h3 class="cc-section-title">Reward Program</h3>
                        <?php if (!empty($rewards)): foreach ($rewards as $reward): ?>
                        <div class="item">
                            <div>
                                <p class="label"><?php echo esc_html($reward['category']); ?></p>
                                <p style="font-size: 0.8rem; color: var(--cc-gray-600);"><?php echo esc_html($reward['description']); ?></p>
                            </div>
                            <span class="value positive"><?php echo esc_html($reward['rate']); ?></span>
                        </div>
                        <?php endforeach; else: echo '<p>No reward details available.</p>'; endif; ?>
                    </div>
                </div>

                <!-- Fees Tab -->
                <div id="fees" class="cc-tab-content">
                     <div class="cc-section cc-data-table">
                        <h3 class="cc-section-title">Fees & Charges</h3>
                        <div class="item"><span class="label">Joining Fee</span><span class="value negative"><?php echo esc_html($joining_fee); ?></span></div>
                        <div class="item"><span class="label">Annual Fee</span><span class="value negative"><?php echo esc_html($annual_fee); ?></span></div>
                        <?php if (!empty($fees)): foreach ($fees as $fee): ?>
                        <div class="item">
                            <span class="label"><?php echo esc_html($fee['type']); ?></span>
                            <span class="value negative"><?php echo esc_html($fee['amount']); ?></span>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <!-- Eligibility Tab -->
                <div id="eligibility" class="cc-tab-content">
                    <div class="cc-section cc-data-table">
                        <h3 class="cc-section-title">Eligibility Criteria</h3>
                         <?php if (!empty($eligibility)): foreach ($eligibility as $criterion): ?>
                         <div class="item">
                             <span class="label"><?php echo esc_html($criterion['criteria']); ?></span>
                             <span class="value"><?php echo esc_html($criterion['value']); ?></span>
                         </div>
                         <?php endforeach; else: echo '<p>No eligibility criteria listed.</p>'; endif; ?>
                    </div>
                    <?php if (!empty($documents)): ?>
                    <div class="cc-section cc-list">
                        <h3 class="cc-section-title"><?php echo get_cc_icon('file-text', 'icon'); ?><span>Required Documents</span></h3>
                        <ul><?php foreach ($documents as $doc) echo '<li><span class="icon">' . get_cc_icon('file-text') . '</span><span>' . esc_html($doc) . '</span></li>'; ?></ul>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Bottom CTA -->
                <div class="cc-bottom-cta">
                    <h3>Ready to Apply?</h3>
                    <p>Join thousands of satisfied customers and start earning rewards today!</p>
                     <a href="<?php echo $apply_link; ?>" target="_blank" rel="noopener sponsored" class="cc-btn">
                        <?php echo get_cc_icon('zap', 'icon'); ?>
                        <span>Apply Now</span>
                        <?php echo get_cc_icon('arrow-right', 'icon'); ?>
                    </a>
                </div>
            </div>
        </article>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.cc-tab-link');
    const tabContents = document.querySelectorAll('.cc-tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const tabId = link.getAttribute('data-tab');

            tabLinks.forEach(item => item.classList.remove('active'));
            link.classList.add('active');

            tabContents.forEach(content => {
                content.classList.toggle('active', content.id === tabId);
            });
        });
    });
});
</script>

<?php
get_footer();
