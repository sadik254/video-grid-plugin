<?php
/*
Plugin Name: Video Grid Plugin
Description: A plugin to manage video grids with a main section and a favourite section.
Version: 1.0.0
Author: Saleh Sadik
Author URI: https://www.linkedin.com/in/sadik254/
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue scripts and styles
function video_grid_plugin_enqueue_scripts($hook_suffix) {
    if ($hook_suffix == 'toplevel_page_video-grid-plugin' || strpos($hook_suffix, 'video-grid') !== false) {
        wp_enqueue_media(); // Enqueue WordPress media library scripts
        wp_enqueue_style('video-grid-plugin-style', plugins_url('css/style.css', __FILE__));
        wp_enqueue_script('jquery');
        wp_enqueue_script('video-grid-plugin-script', plugins_url('js/script.js', __FILE__), array('jquery'), '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'video_grid_plugin_enqueue_scripts');

function video_grid_plugin_enqueue_frontend_scripts() {
    wp_enqueue_style('video-grid-plugin-frontend-style', plugins_url('css/frontend-style.css', __FILE__));
    wp_enqueue_script('jquery');
    wp_enqueue_script('video-grid-plugin-frontend-script', plugins_url('js/script.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('video-grid-plugin-frontend-script', 'videoGridPlugin', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'video_grid_plugin_enqueue_frontend_scripts');

// Add the plugin menu
function video_grid_plugin_menu() {
    add_menu_page('Video Grid Plugin', 'Video Grid', 'manage_options', 'video-grid-plugin', 'video_grid_plugin_main_page', 'dashicons-video-alt3', 6);
    add_submenu_page('video-grid-plugin', 'Main Section', 'Main Section', 'manage_options', 'video-grid-main', 'video_grid_plugin_main_page');
    add_submenu_page('video-grid-plugin', 'Favourite Section', 'Favourite Section', 'manage_options', 'video-grid-favourite', 'video_grid_plugin_favourite_page');
}
add_action('admin_menu', 'video_grid_plugin_menu');

// Main section page
function video_grid_plugin_main_page() {
    ?>
    <div class="wrap">
        <h1>Main Section</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('video_grid_plugin_main_options');
            do_settings_sections('video_grid_main');
            submit_button('Save Changes');
            ?>
        </form>
        <div class="shortcode-info">
            <h2>Shortcode for Main Section Videos</h2>
            <p>Use the following shortcode to display the Main Section videos on your pages or posts:</p>
            <code>[main_videos]</code>
        </div>
    </div>
    <?php
}

// Favourite section page
function video_grid_plugin_favourite_page() {
    ?>
    <div class="wrap">
        <h1>Favourite Section</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('video_grid_plugin_favourite_options');
            do_settings_sections('video_grid_favourite');
            submit_button('Save Changes');
            ?>
        </form>
        <div class="shortcode-info">
            <h2>Shortcode for Favourite Section Videos</h2>
            <p>Use the following shortcode to display the Favourite Section videos on your pages or posts:</p>
            <code>[favourite_videos]</code>
        </div>
    </div>
    <?php
}


// Register settings and sections
function video_grid_plugin_register_settings() {
    // Main Section
    register_setting('video_grid_plugin_main_options', 'video_grid_plugin_main_videos', 'sanitize_video_grid');
    add_settings_section('video_grid_main_section', 'Main Videos', 'video_grid_main_section_callback', 'video_grid_main');
    add_settings_field('video_grid_main_field', 'Main Videos', 'video_grid_main_field_callback', 'video_grid_main', 'video_grid_main_section');

    // Favourite Section
    register_setting('video_grid_plugin_favourite_options', 'video_grid_plugin_favourite_videos', 'sanitize_video_grid');
    add_settings_section('video_grid_favourite_section', 'Favourite Videos', 'video_grid_favourite_section_callback', 'video_grid_favourite');
    add_settings_field('video_grid_favourite_field', 'Favourite Videos', 'video_grid_favourite_field_callback', 'video_grid_favourite', 'video_grid_favourite_section');
}
add_action('admin_init', 'video_grid_plugin_register_settings');

function video_grid_main_section_callback() {
    echo 'Manage videos for the main section.';
}

function video_grid_favourite_section_callback() {
    echo 'Manage your favourite videos.';
}

// Display the video grid for the main section
function video_grid_main_field_callback() {
    $videos = get_option('video_grid_plugin_main_videos', []);
    video_grid_plugin_display_grid($videos, 'video_grid_plugin_main_videos');
}

// Display the video grid for the favourite section
function video_grid_favourite_field_callback() {
    $videos = get_option('video_grid_plugin_favourite_videos', []);
    video_grid_plugin_display_grid($videos, 'video_grid_plugin_favourite_videos');
}

// Display video grid helper function
function video_grid_plugin_display_grid($videos, $option_name) {
    ?>
    <div class="video-grid-wrapper" id="<?php echo esc_attr($option_name); ?>">
        <button type="button" class="button button-secondary add-video">Add Video</button>
        <div class="video-grid">
            <?php if (!empty($videos)) : ?>
                <?php foreach ($videos as $index => $video) : ?>
                    <div class="video-grid-item">
                    <input type="hidden" name="<?php echo esc_attr($option_name); ?>[<?php echo $index; ?>][url]" value="<?php echo esc_attr($video['url']); ?>" />
                    <video width="320" height="240" controls>
                        <source src="<?php echo esc_url($video['url']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <input type="text" name="<?php echo esc_attr($option_name); ?>[<?php echo $index; ?>][title]" value="<?php echo esc_attr($video['title']); ?>" placeholder="Enter video title" />
                    <input type="text" name="<?php echo esc_attr($option_name); ?>[<?php echo $index; ?>][subtitle]" value="<?php echo esc_attr($video['subtitle']); ?>" placeholder="Enter video subtitle" />
                    <button type="button" class="button button-secondary replace-video">Replace</button>
                    <button type="button" class="button button-link remove-video">Remove</button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// Sanitize the video grid input
function sanitize_video_grid($input) {
    if (!is_array($input)) {
        return [];
    }

    $output = [];
    foreach ($input as $key => $video) {
        if (isset($video['url']) && !empty($video['url'])) {
            $output[$key] = [
                'url' => esc_url_raw($video['url']),
                'title' => sanitize_text_field($video['title']),
                'subtitle' => sanitize_text_field($video['subtitle']),
            ];
        }
    }
    return $output;
}
// Register shortcodes
function video_grid_plugin_register_shortcodes() {
    add_shortcode('main_videos', 'video_grid_plugin_display_main_videos');
    add_shortcode('favourite_videos', 'video_grid_plugin_display_favourite_videos');
}
add_action('init', 'video_grid_plugin_register_shortcodes');

// Display Main Section Videos
function video_grid_plugin_display_main_videos($atts) {
    return video_grid_plugin_display_videos('video_grid_plugin_main_videos');
}

// Display Favourite Section Videos
function video_grid_plugin_display_favourite_videos($atts) {
    return video_grid_plugin_display_videos('video_grid_plugin_favourite_videos');
}

// Helper function to display videos
function video_grid_plugin_display_videos($option_name) {
    $videos = get_option($option_name, []);
    if (empty($videos)) {
        return '<p>No videos available.</p>';
    }
    ob_start();
    ?>
    <div class="video-grid-frontend">
        <?php foreach ($videos as $index => $video) : ?>
            <div class="video-grid-item-frontend" data-video-index="<?php echo esc_attr($index); ?>">
                <div class="video-container">
                    <video width="320" height="240" data-src="<?php echo esc_url($video['url']); ?>">
                        <source src="<?php echo esc_url($video['url']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="video-overlay-frontend">
                        <div class="play-button"></div>
                    </div>
                    <div class="video-info-frontend">
                        <div class="video-title-frontend">
                            <?php echo esc_html($video['title']); ?>
                        </div>
                        <div class="video-subtitle-frontend">
                            <?php echo esc_html($video['subtitle']); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
// Enqueue frontend styles
function video_grid_plugin_enqueue_frontend_styles() {
    wp_enqueue_style('video-grid-plugin-frontend-style', plugins_url('css/frontend-style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'video_grid_plugin_enqueue_frontend_styles');

// Add fullscreen overlay container to the footer
function video_grid_plugin_add_overlay_container() {
    echo '<div id="video-grid-fullscreen-overlay-container"></div>';
}
add_action('wp_footer', 'video_grid_plugin_add_overlay_container');