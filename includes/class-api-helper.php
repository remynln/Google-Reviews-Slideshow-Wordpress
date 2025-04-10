<?php
/**
 * API Helper class for Google Reviews Slideshow
 */
class Google_Reviews_Slideshow_API_Helper {
    
    public function fetch_reviews($place_id, $api_key) {
        error_log('Google Reviews Slideshow: Fetching reviews for place ID: ' . $place_id);
        $url = add_query_arg(
            array(
                'place_id' => $place_id,
                'fields' => 'name,rating,reviews,user_ratings_total',
                'key' => $api_key
            ),
            'https://maps.googleapis.com/maps/api/place/details/json'
        );
        
        $response = wp_remote_get($url);
        
        error_log('Google Reviews Slideshow: API response: ' . print_r($response, true));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data['status'] !== 'OK') {
            return new WP_Error('api_error', __('Error fetching reviews: ', 'google-reviews-slideshow') . $data['status']);
        }
        
        $result = array(
            'place_name' => isset($data['result']['name']) ? $data['result']['name'] : '',
            'rating' => isset($data['result']['rating']) ? $data['result']['rating'] : 0,
            'total_ratings' => isset($data['result']['user_ratings_total']) ? $data['result']['user_ratings_total'] : 0,
            'reviews' => isset($data['result']['reviews']) ? $data['result']['reviews'] : array()
        );

        error_log('Google Reviews Slideshow: Processed reviews data: ' . print_r($result, true));
        
        return $result;
    }
}
