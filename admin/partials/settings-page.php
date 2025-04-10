<?php
/**
 * Provides the markup for the settings page
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    Google_Reviews_Slideshow
 * @subpackage Google_Reviews_Slideshow/admin/partials
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully.', 'google-reviews-slideshow') . '</p></div>';
        
        if (isset($_POST['update_reviews_now']) && $_POST['update_reviews_now'] == '1') {
            do_action('google_reviews_slideshow_daily_update');
            echo '<div class="notice notice-info is-dismissible"><p>' . __('Reviews have been updated.', 'google-reviews-slideshow') . '</p></div>';
        }
    }
    
    if (isset($_GET['update_reviews']) && $_GET['update_reviews'] == 'true') {
        do_action('google_reviews_slideshow_daily_update');
        echo '<div class="notice notice-info is-dismissible"><p>' . __('Reviews have been updated.', 'google-reviews-slideshow') . '</p></div>';
    }
    ?>
    
    <div class="google-reviews-slideshow-admin">
        <div class="google-reviews-slideshow-settings">
            <form method="post" action="options.php">
                <?php
                settings_fields('google_reviews_slideshow_settings_group');
                
                do_settings_sections($this->plugin_name);
                
                echo '<input type="hidden" id="update_reviews_now" name="update_reviews_now" value="0">';
                
                submit_button(__('Save Settings', 'google-reviews-slideshow'), 'primary', 'submit', true);
                
                submit_button(__('Save Settings & Update Reviews Now', 'google-reviews-slideshow'), 'secondary', 'update_now', true, array('id' => 'update-reviews-button'));
                ?>
            </form>
            
            <!-- Standalone Update Reviews Button -->
            <div class="update-reviews-standalone">
                <h3><?php _e('Update Reviews', 'google-reviews-slideshow'); ?></h3>
                <p><?php _e('Click the button below to update reviews without changing settings:', 'google-reviews-slideshow'); ?></p>
                <form method="get">
                    <input type="hidden" name="page" value="<?php echo esc_attr($this->plugin_name); ?>">
                    <input type="hidden" name="update_reviews" value="true">
                    <?php submit_button(__('Update Reviews Now', 'google-reviews-slideshow'), 'secondary', 'update_reviews_button', true); ?>
                </form>
            </div>
        </div>
        
        <div class="google-reviews-slideshow-sidebar">
            <div class="google-reviews-slideshow-box">
                <h3><?php _e('How to Use', 'google-reviews-slideshow'); ?></h3>
                <p><?php _e('To display the Google Reviews Slideshow on your site, use the following shortcode:', 'google-reviews-slideshow'); ?></p>
                <code>[google_reviews_slideshow]</code>
                
                <p><?php _e('You can also customize the width and height:', 'google-reviews-slideshow'); ?></p>
                <code>[google_reviews_slideshow width="100%" height="400px"]</code>
            </div>
            
            <div class="google-reviews-slideshow-box">
                <h3><?php _e('Need Help?', 'google-reviews-slideshow'); ?></h3>
                <p><?php _e('For help setting up your Google API key and finding your Place ID, check out these resources:', 'google-reviews-slideshow'); ?></p>
                <ul>
                    <li><a href="https://developers.google.com/maps/documentation/places/web-service/get-api-key" target="_blank"><?php _e('Get a Google API Key', 'google-reviews-slideshow'); ?></a></li>
                    <li><a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank"><?php _e('Find your Place ID', 'google-reviews-slideshow'); ?></a></li>
                </ul>
            </div>
            
            <div class="google-reviews-slideshow-box">
                <h3><?php _e('Last Update', 'google-reviews-slideshow'); ?></h3>
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'google_reviews_slideshow';
                
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
                
                if ($table_exists) {
                    $place = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1");
                    
                    if ($place) {
                        $last_updated = strtotime($place->last_updated);
                        echo '<p>' . sprintf(__('Reviews were last updated: %s', 'google-reviews-slideshow'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_updated)) . '</p>';
                    } else {
                        echo '<p>' . __('Reviews have not been updated yet.', 'google-reviews-slideshow') . '</p>';
                    }
                } else {
                    echo '<p>' . __('Database tables not found. Please deactivate and reactivate the plugin.', 'google-reviews-slideshow') . '</p>';
                }
                ?>
            </div>
            
            <div class="google-reviews-slideshow-box">
                <h3><?php _e('Plugin Status', 'google-reviews-slideshow'); ?></h3>
                <?php
                $tables_status = $table_exists ? 
                    '<span class="status-ok">✓ Database tables exist</span>' : 
                    '<span class="status-error">✗ Database tables missing</span>';
                
                $settings = get_option('google_reviews_slideshow_settings');
                $api_key = isset($settings['api_key']) && !empty($settings['api_key']) ? 
                    '<span class="status-ok">✓ API Key configured</span>' : 
                    '<span class="status-error">✗ API Key missing</span>';
                
                $place_id = isset($settings['place_id']) && !empty($settings['place_id']) ? 
                    '<span class="status-ok">✓ Place ID configured</span>' : 
                    '<span class="status-error">✗ Place ID missing</span>';
                ?>
                
                <ul class="status-list">
                    <li><?php echo $tables_status; ?></li>
                    <li><?php echo $api_key; ?></li>
                    <li><?php echo $place_id; ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .google-reviews-slideshow-admin {
        display: flex;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .google-reviews-slideshow-settings {
        flex: 0 0 65%;
        padding-right: 30px;
        box-sizing: border-box;
    }
    
    .google-reviews-slideshow-sidebar {
        flex: 0 0 35%;
    }
    
    .google-reviews-slideshow-box {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,0.04);
        margin-bottom: 20px;
        padding: 15px;
    }
    
    .google-reviews-slideshow-box h3 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .google-reviews-slideshow-box code {
        display: block;
        padding: 10px;
        margin: 10px 0;
        background: #f5f5f5;
        border: 1px solid #e5e5e5;
    }
    
    .form-table th {
        width: 200px;
    }
    
    .update-reviews-standalone {
        margin-top: 30px;
        padding: 15px;
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,0.04);
    }
    
    .update-reviews-standalone h3 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .status-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    
    .status-list li {
        margin-bottom: 8px;
    }
    
    .status-ok {
        color: #46b450;
        font-weight: 500;
    }
    
    .status-error {
        color: #dc3232;
        font-weight: 500;
    }
    
    @media (max-width: 782px) {
        .google-reviews-slideshow-settings,
        .google-reviews-slideshow-sidebar {
            flex: 0 0 100%;
        }
        
        .google-reviews-slideshow-settings {
            padding-right: 0;
            margin-bottom: 30px;
        }
    }
</style>

<script>
jQuery(document).ready(function($) {
    $('#update-reviews-button').on('click', function() {
        console.log('Google Reviews Slideshow: Update reviews button clicked.');
        $('#update_reviews_now').val('1');
    });
});
</script>