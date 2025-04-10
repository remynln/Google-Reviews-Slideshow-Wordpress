<div class="google-reviews-slideshow" style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;">
    <?php if ($place && $reviews): ?>
        <div class="google-reviews-container">
            <div class="google-reviews-summary">
                <div class="google-reviews-place">
                    <h3><?php echo esc_html($place->place_name); ?></h3>
                    <div class="google-reviews-rating">
                        <div class="google-reviews-stars">
                            <?php
                            $rating = round($place->overall_rating * 2) / 2; // Round to nearest 0.5
                            $full_stars = floor($rating);
                            $half_star = ($rating - $full_stars) >= 0.5;
                            $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                            
                            for ($i = 0; $i < $full_stars; $i++) {
                                echo '<span class="dashicons dashicons-star-filled"></span>';
                            }
                            
                            if ($half_star) {
                                echo '<span class="dashicons dashicons-star-half"></span>';
                            }
                            
                            for ($i = 0; $i < $empty_stars; $i++) {
                                echo '<span class="dashicons dashicons-star-empty"></span>';
                            }
                            ?>
                        </div>
                        <div class="google-reviews-rating-value">
                            <?php echo esc_html(number_format($place->overall_rating, 1)); ?> / 5
                        </div>
                    </div>
                    <div class="google-reviews-count">
                        <?php printf(
                            _n(
                                'Based on %d review',
                                'Based on %d reviews',
                                $place->total_ratings,
                                'google-reviews-slideshow'
                            ),
                            $place->total_ratings
                        ); ?>
                    </div>
                    <div class="google-reviews-powered-by">
                        <?php _e('Powered by', 'google-reviews-slideshow'); ?>
                        <a href="https://www.google.com/business/" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url($this->plugin_url . 'public/images/google-logo.png'); ?>" alt="Google" />
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="google-reviews-slideshow-container">
                <div class="google-reviews-slides">
                    <?php foreach ($reviews as $review): ?>
                        <div class="google-review-slide">
                            <div class="google-review-header">
                                <div class="google-review-author">
                                    <?php if (!empty($review->author_photo)): ?>
                                        <img src="<?php echo esc_url($review->author_photo); ?>" alt="<?php echo esc_attr($review->author_name); ?>" />
                                    <?php endif; ?>
                                    <span class="google-review-author-name"><?php echo esc_html($review->author_name); ?></span>
                                </div>
                                <div class="google-review-rating">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $review->rating) {
                                            echo '<span class="dashicons dashicons-star-filled"></span>';
                                        } else {
                                            echo '<span class="dashicons dashicons-star-empty"></span>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="google-review-content">
                                <?php echo esc_html($review->text); ?>
                            </div>
                            <div class="google-review-time">
                                <?php echo esc_html($review->relative_time); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="google-reviews-error">
            <?php _e('No reviews found. Please check your settings or try updating the reviews.', 'google-reviews-slideshow'); ?>
        </div>
    <?php endif; ?>
</div>
