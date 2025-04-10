<?php
/**
 * Plugin Name: Google Reviews Slideshow
 * Plugin URI: https://github.com/remynln/Google-Reviews-Slideshow-Wordpress
 * Description: Displays Google store reviews with ratings summary and slideshow that updates daily.
 * Version: 1.0.0
 * Author: Noulin Remy
 * Author URI: https://remynln.fr
 * Text Domain: google-reviews-slideshow
 * Domain Path: /languages
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Google_Reviews_Slideshow {
    private static $instance = null;
    private $plugin_path;
    private $plugin_url;
    private $version = '1.0.0';
    
    private function __construct() {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);
        
        // Load dependencies
        $this->load_dependencies();
        
        // Define hooks
        $this->define_admin_hooks();
        $this->define_public_hooks();
        
        // Setup cron job for daily updates
        $this->setup_cron();
    }
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function load_dependencies() {
        // Include admin class
        require_once $this->plugin_path . 'admin/class-admin.php';
        
        // Include API helper class
        require_once $this->plugin_path . 'includes/class-api-helper.php';
        
        // Include database handler
        require_once $this->plugin_path . 'includes/class-db-handler.php';
        
        // Include public display class
        require_once $this->plugin_path . 'public/class-public-display.php';
    }
    
    private function define_admin_hooks() {
        // Initialize admin class
        $admin = new Google_Reviews_Slideshow_Admin($this->get_plugin_name(), $this->get_version(), $this->plugin_path, $this->plugin_url);
        
        // Add admin menu
        add_action('admin_menu', array($admin, 'add_menu_page'));
        
        // Register settings
        add_action('admin_init', array($admin, 'register_settings'));
        
        // Add settings link on plugin page
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($admin, 'add_settings_link'));
    }
    
    private function define_public_hooks() {
        // Initialize public display class
        $public = new Google_Reviews_Slideshow_Public($this->get_plugin_name(), $this->get_version(), $this->plugin_path, $this->plugin_url);
        
        // Register shortcode
        add_shortcode('google_reviews_slideshow', array($public, 'render_shortcode'));
        
        // Enqueue styles and scripts
        add_action('wp_enqueue_scripts', array($public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($public, 'enqueue_scripts'));
    }
    
    private function setup_cron() {
        // Register cron event
        if (!wp_next_scheduled('google_reviews_slideshow_daily_update')) {
            wp_schedule_event(time(), 'daily', 'google_reviews_slideshow_daily_update');
        }
        
        // Add cron action
        add_action('google_reviews_slideshow_daily_update', array($this, 'update_reviews'));
    }
    
    public function update_reviews() {
        // Log error when starting update
        error_log('Google Reviews Slideshow: Starting daily update of reviews.');
        // Get API helper
        $api_helper = new Google_Reviews_Slideshow_API_Helper();
        
        // Get DB handler
        $db_handler = new Google_Reviews_Slideshow_DB_Handler();
        
        // Get settings
        $settings = get_option('google_reviews_slideshow_settings');
        
        // Get place ID
        $place_id = isset($settings['place_id']) ? $settings['place_id'] : '';
        
        // Get API key
        $api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
        
        if (!empty($place_id) && !empty($api_key)) {
            // Fetch reviews
            $reviews = $api_helper->fetch_reviews($place_id, $api_key);
            
            if (!is_wp_error($reviews)) {
                // Save reviews to database
                $db_handler->save_reviews($reviews);
            }
        }
    }
    
    public function get_plugin_name() {
        return 'google-reviews-slideshow';
    }
    
    public function get_version() {
        return $this->version;
    }
}

// Initialize the plugin
function run_google_reviews_slideshow() {
    Google_Reviews_Slideshow::get_instance();
}
run_google_reviews_slideshow();
