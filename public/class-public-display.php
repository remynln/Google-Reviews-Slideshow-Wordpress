<?php
/**
 * Public Display class for Google Reviews Slideshow
 */
class Google_Reviews_Slideshow_Public {
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
    
    public function enqueue_styles() {
        wp_enqueue_style('dashicons');
        
        wp_enqueue_style(
            $this->plugin_name,
            $this->plugin_url . 'public/css/public.css',
            array(),
            $this->version,
            'all'
        );
        
        wp_enqueue_style(
            $this->plugin_name . '-slick',
            'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',
            array(),
            '1.8.1',
            'all'
        );
        
        wp_enqueue_style(
            $this->plugin_name . '-slick-theme',
            'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css',
            array(),
            '1.8.1',
            'all'
        );
        
        $settings = get_option('google_reviews_slideshow_settings');
        if (isset($settings['custom_css']) && !empty($settings['custom_css'])) {
            wp_add_inline_style($this->plugin_name, $settings['custom_css']);
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        
        wp_enqueue_script(
            $this->plugin_name . '-slick',
            'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
            array('jquery'),
            '1.8.1',
            true
        );
        
        wp_enqueue_script(
            $this->plugin_name,
            $this->plugin_url . 'public/js/public.js',
            array('jquery', $this->plugin_name . '-slick'),
            $this->version,
            true
        );
        
        $settings = get_option('google_reviews_slideshow_settings');
        $slideshow_speed = isset($settings['slideshow_speed']) ? $settings['slideshow_speed'] : 5000;
        
        wp_localize_script(
            $this->plugin_name,
            'GoogleReviewsSlideshow',
            array(
                'slideshowSpeed' => $slideshow_speed
            )
        );
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'width' => '100%',
                'height' => 'auto',
            ),
            $atts,
            'google_reviews_slideshow'
        );
        
        $db_handler = new Google_Reviews_Slideshow_DB_Handler();
        
        $place = $db_handler->get_place_info();
        
        $reviews = $db_handler->get_reviews();
        
        ob_start();
        
        include $this->plugin_path . 'public/partials/display.php';
        
        return ob_get_clean();
    }
}
