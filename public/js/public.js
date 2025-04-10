/**
 * Public JavaScript for Google Reviews Slideshow
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        $('.google-reviews-slides').slick({
            dots: true,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear',
            autoplay: true,
            autoplaySpeed: GoogleReviewsSlideshow.slideshowSpeed,
            arrows: true,
            adaptiveHeight: true,
            responsive: [
                {
                    breakpoint: 768,
                    settings: {
                        arrows: false
                    }
                }
            ]
        });
    });
    
})(jQuery);
