(function ($) {
    $(document).ready(function () {
        if (jQuery().magnificPopup) {
            /* open lightbox when click to image */
            $('.open-lightbox-feature-image').magnificPopup({
                gallery: {
                    enabled: true,
                    tCounter: '<span class="wpmf_mfp-counter">%curr% / %total%</span>',
                    arrowMarkup: '<button title="%title%" type="button" class="zmdi zmdi-chevron-%dir%"></button>' // markup of an arrow button
                },
                callbacks: {
                    elementParse: function (q) {

                        q.src = q.el.attr('src');

                    }
                },
                type: 'image',
                showCloseBtn: false,
                image: {
                    titleSrc: 'title'
                }
            });

            /* open lightbox when click to image */
            $('body a').each(function(i,v){
                if($(v).find('img[data-wpmflightbox="1"]').length != 0){
                    $(v).magnificPopup({
                        delegate: 'img',
                        gallery: {
                            enabled: true,
                            tCounter: '<span class="wpmf_mfp-counter">%curr% / %total%</span>',
                            arrowMarkup: '<button title="%title%" type="button" class="zmdi zmdi-chevron-%dir%"></button>' // markup of an arrow button
                        },
                        callbacks: {
                            elementParse: function(q) { 
                                var wpmf_lightbox = q.el.data('wpmf_image_lightbox');
                                if(typeof wpmf_lightbox == "undefined"){
                                    q.src = q.el.attr('src'); 
                                }else{
                                    q.src = wpmf_lightbox; 
                                }
                            }
                        }
        ,
                        type: 'image',
                        showCloseBtn : false,
                        image: {
                            titleSrc: 'title'
                        }
                    });
                }
            });
        }
    });
})(jQuery);