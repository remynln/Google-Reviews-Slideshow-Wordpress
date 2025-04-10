<?php
/**
 * Database Handler class for Google Reviews Slideshow
 */

register_activation_hook(__FILE__, 'google_reviews_slideshow_activate');

function google_reviews_slideshow_activate() {
    // Create an instance of the DB handler class
    require_once plugin_dir_path(__FILE__) . 'includes/class-db-handler.php';
    $db_handler = new Google_Reviews_Slideshow_DB_Handler();
    
    // Force table creation
    if (method_exists($db_handler, 'create_tables')) {
        $db_handler->create_tables();
    }
    
    // Add a version option to track updates
    add_option('google_reviews_slideshow_version', '1.0.0');
}
 

class Google_Reviews_Slideshow_DB_Handler {
    
    public function __construct() {
        $this->create_tables();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_name = $wpdb->prefix . 'google_reviews_slideshow';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            place_id varchar(255) NOT NULL,
            place_name varchar(255) NOT NULL,
            overall_rating float NOT NULL,
            total_ratings int NOT NULL,
            last_updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        $reviews_table = $wpdb->prefix . 'google_reviews_slideshow_reviews';
        
        $sql .= "CREATE TABLE $reviews_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            place_id varchar(255) NOT NULL,
            review_id varchar(255) NOT NULL,
            author_name varchar(255) NOT NULL,
            author_photo varchar(255) NOT NULL,
            rating int NOT NULL,
            text text NOT NULL,
            time int NOT NULL,
            relative_time varchar(255) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY review_id (review_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    
    
    public function save_reviews($data) {
        global $wpdb;
        
        $settings = get_option('google_reviews_slideshow_settings');
        $place_id = isset($settings['place_id']) ? $settings['place_id'] : '';
        
        if (empty($place_id)) {
            return false;
        }
        
        $this->update_place_info($place_id, $data);
        
        if (!empty($data['reviews'])) {
            $this->update_reviews($place_id, $data['reviews']);
        }
        
        return true;
    }
    
    private function update_place_info($place_id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'google_reviews_slideshow';
        
        $place = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE place_id = %s",
            $place_id
        ));
        
        $current_time = current_time('mysql');
        
        if ($place) {
            $wpdb->update(
                $table_name,
                array(
                    'place_name' => $data['place_name'],
                    'overall_rating' => $data['rating'],
                    'total_ratings' => $data['total_ratings'],
                    'last_updated' => $current_time
                ),
                array('place_id' => $place_id),
                array('%s', '%f', '%d', '%s'),
                array('%s')
            );
        } else {
            $wpdb->insert(
                $table_name,
                array(
                    'place_id' => $place_id,
                    'place_name' => $data['place_name'],
                    'overall_rating' => $data['rating'],
                    'total_ratings' => $data['total_ratings'],
                    'last_updated' => $current_time
                ),
                array('%s', '%s', '%f', '%d', '%s')
            );
        }
    }
    
    private function update_reviews($place_id, $reviews) {
        global $wpdb;
        
        $reviews_table = $wpdb->prefix . 'google_reviews_slideshow_reviews';
        
        foreach ($reviews as $review) {
            $review_id = $review['time'] . '-' . md5($review['author_name']);
            
            $existing_review = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $reviews_table WHERE review_id = %s",
                $review_id
            ));
            
            if ($existing_review) {
                $wpdb->update(
                    $reviews_table,
                    array(
                        'author_name' => $review['author_name'],
                        'author_photo' => $review['profile_photo_url'],
                        'rating' => $review['rating'],
                        'text' => $review['text'],
                        'time' => $review['time'],
                        'relative_time' => $review['relative_time_description']
                    ),
                    array('review_id' => $review_id),
                    array('%s', '%s', '%d', '%s', '%d', '%s'),
                    array('%s')
                );
            } else {
                $wpdb->insert(
                    $reviews_table,
                    array(
                        'place_id' => $place_id,
                        'review_id' => $review_id,
                        'author_name' => $review['author_name'],
                        'author_photo' => $review['profile_photo_url'],
                        'rating' => $review['rating'],
                        'text' => $review['text'],
                        'time' => $review['time'],
                        'relative_time' => $review['relative_time_description']
                    ),
                    array('%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s')
                );
            }
        }
    }
    
    public function get_place_info() {
        global $wpdb;
        
        $settings = get_option('google_reviews_slideshow_settings');
        $place_id = isset($settings['place_id']) ? $settings['place_id'] : '';
        
        if (empty($place_id)) {
            return false;
        }
        
        $table_name = $wpdb->prefix . 'google_reviews_slideshow';
        
        $place = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE place_id = %s",
            $place_id
        ));
        
        return $place;
    }
    
    public function get_reviews() {
        global $wpdb;
        
        $settings = get_option('google_reviews_slideshow_settings');
        $place_id = isset($settings['place_id']) ? $settings['place_id'] : '';
        $minimum_rating = isset($settings['minimum_rating']) ? $settings['minimum_rating'] : 1;
        $reviews_count = isset($settings['reviews_count']) ? $settings['reviews_count'] : 5;
        
        if (empty($place_id)) {
            return array();
        }
        
        $reviews_table = $wpdb->prefix . 'google_reviews_slideshow_reviews';
        
        $reviews = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $reviews_table WHERE place_id = %s AND rating >= %d ORDER BY time DESC LIMIT %d",
            $place_id,
            $minimum_rating,
            $reviews_count
        ));
        
        return $reviews;
    }
}
