<?php
/**
 * Admin class for Google Reviews Slideshow
 */
class Google_Reviews_Slideshow_Admin {
    private $plugin_name;
    private $version;
    private $plugin_path;
    private $plugin_url;
    
    public function __construct($plugin_name, $version, $plugin_path, $plugin_url) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->plugin_path = $plugin_path;
        $this->plugin_url = $plugin_url;
    }
    
    public function add_menu_page() {
        add_menu_page(
            __('Google Reviews Slideshow', 'google-reviews-slideshow'),
            __('Google Reviews', 'google-reviews-slideshow'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_settings_page'),
            'dashicons-google',
            90
        );
    }
    
    public function display_settings_page() {
        include_once $this->plugin_path . 'admin/partials/settings-page.php';
    }
    
    public function register_settings() {
        register_setting(
            'google_reviews_slideshow_settings_group',
            'google_reviews_slideshow_settings',
            array($this, 'sanitize_settings')
        );
        
        add_settings_section(
            'google_reviews_slideshow_general_section',
            __('Google API Settings', 'google-reviews-slideshow'),
            array($this, 'general_section_callback'),
            $this->plugin_name
        );
        
        add_settings_field(
            'place_id',
            __('Google Place ID', 'google-reviews-slideshow'),
            array($this, 'place_id_callback'),
            $this->plugin_name,
            'google_reviews_slideshow_general_section'
        );
        
        add_settings_field(
            'api_key',
            __('Google API Key', 'google-reviews-slideshow'),
            array($this, 'api_key_callback'),
            $this->plugin_name,
            'google_reviews_slideshow_general_section'
        );
        
        add_settings_section(
            'google_reviews_slideshow_display_section',
            __('Display Settings', 'google-reviews-slideshow'),
            array($this, 'display_section_callback'),
            $this->plugin_name
        );
        
        add_settings_field(
            'minimum_rating',
            __('Minimum Rating', 'google-reviews-slideshow'),
            array($this, 'minimum_rating_callback'),
            $this->plugin_name,
            'google_reviews_slideshow_display_section'
        );
        
        add_settings_field(
            'reviews_count',
            __('Number of Reviews', 'google-reviews-slideshow'),
            array($this, 'reviews_count_callback'),
            $this->plugin_name,
            'google_reviews_slideshow_display_section'
        );
        
        add_settings_field(
            'slideshow_speed',
            __('Slideshow Speed (ms)', 'google-reviews-slideshow'),
            array($this, 'slideshow_speed_callback'),
            $this->plugin_name,
            'google_reviews_slideshow_display_section'
        );
        
        add_settings_field(
            'custom_css',
            __('Custom CSS', 'google-reviews-slideshow'),
            array($this, 'custom_css_callback'),
            $this->plugin_name,
            'google_reviews_slideshow_display_section'
        );
    }
    
    public function general_section_callback() {
        echo '<p>' . __('Configure your Google API settings to fetch reviews.', 'google-reviews-slideshow') . '</p>';
    }
    
    public function display_section_callback() {
        echo '<p>' . __('Customize how your reviews appear on your site.', 'google-reviews-slideshow') . '</p>';
    }
    
    public function place_id_callback() {
        $settings = get_option('google_reviews_slideshow_settings');
        $place_id = isset($settings['place_id']) ? $settings['place_id'] : '';
        echo '<input type="text" id="place_id" name="google_reviews_slideshow_settings[place_id]" value="' . esc_attr($place_id) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your Google Place ID. <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">How to find your Place ID</a>', 'google-reviews-slideshow') . '</p>';
    }
    
    public function api_key_callback() {
        $settings = get_option('google_reviews_slideshow_settings');
        $api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
        echo '<input type="text" id="api_key" name="google_reviews_slideshow_settings[api_key]" value="' . esc_attr($api_key) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your Google Places API Key. <a href="https://developers.google.com/maps/documentation/places/web-service/get-api-key" target="_blank">Get an API Key</a>', 'google-reviews-slideshow') . '</p>';
    }
    
    public function minimum_rating_callback() {
        $settings = get_option('google_reviews_slideshow_settings');
        $minimum_rating = isset($settings['minimum_rating']) ? $settings['minimum_rating'] : '1';
        echo '<select id="minimum_rating" name="google_reviews_slideshow_settings[minimum_rating]">';
        for ($i = 1; $i <= 5; $i++) {
            echo '<option value="' . $i . '" ' . selected($minimum_rating, $i, false) . '>' . $i . ' ' . _n('Star', 'Stars', $i, 'google-reviews-slideshow') . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Only show reviews with this rating or higher.', 'google-reviews-slideshow') . '</p>';
    }
    
    public function reviews_count_callback() {
        $settings = get_option('google_reviews_slideshow_settings');
        $reviews_count = isset($settings['reviews_count']) ? $settings['reviews_count'] : '5';
        echo '<input type="number" id="reviews_count" name="google_reviews_slideshow_settings[reviews_count]" value="' . esc_attr($reviews_count) . '" min="1" max="50" step="1" />';
        echo '<p class="description">' . __('Number of reviews to display in the slideshow.', 'google-reviews-slideshow') . '</p>';
    }
    
    public function slideshow_speed_callback() {
        $settings = get_option('google_reviews_slideshow_settings');
        $slideshow_speed = isset($settings['slideshow_speed']) ? $settings['slideshow_speed'] : '5000';
        echo '<input type="number" id="slideshow_speed" name="google_reviews_slideshow_settings[slideshow_speed]" value="' . esc_attr($slideshow_speed) . '" min="1000" step="500" />';
        echo '<p class="description">' . __('Time in milliseconds between slide transitions.', 'google-reviews-slideshow') . '</p>';
    }
    
    public function custom_css_callback() {
        $settings = get_option('google_reviews_slideshow_settings');
        $custom_css = isset($settings['custom_css']) ? $settings['custom_css'] : '';
        echo '<textarea id="custom_css" name="google_reviews_slideshow_settings[custom_css]" rows="5" cols="50" class="large-text code">' . esc_textarea($custom_css) . '</textarea>';
        echo '<p class="description">' . __('Add custom CSS to style the slideshow.', 'google-reviews-slideshow') . '</p>';
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['place_id'])) {
            $sanitized['place_id'] = sanitize_text_field($input['place_id']);
        }
        
        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        }
        
        if (isset($input['minimum_rating'])) {
            $sanitized['minimum_rating'] = absint($input['minimum_rating']);
            if ($sanitized['minimum_rating'] < 1 || $sanitized['minimum_rating'] > 5) {
                $sanitized['minimum_rating'] = 1;
            }
        }
        
        if (isset($input['reviews_count'])) {
            $sanitized['reviews_count'] = absint($input['reviews_count']);
            if ($sanitized['reviews_count'] < 1) {
                $sanitized['reviews_count'] = 5;
            }
        }
        
        if (isset($input['slideshow_speed'])) {
            $sanitized['slideshow_speed'] = absint($input['slideshow_speed']);
            if ($sanitized['slideshow_speed'] < 1000) {
                $sanitized['slideshow_speed'] = 5000;
            }
        }
        
        if (isset($input['custom_css'])) {
            $sanitized['custom_css'] = wp_strip_all_tags($input['custom_css']);
        }
        
        return $sanitized;
    }
    
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=' . $this->plugin_name . '">' . __('Settings', 'google-reviews-slideshow') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
