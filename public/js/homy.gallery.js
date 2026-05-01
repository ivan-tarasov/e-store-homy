(function ($) {
    'use strict';

    if (!$ || !$.fn || !$.fn.owlCarousel) {
        return;
    }

    var $main = $('#product-gallery-main');
    if ($main.length === 0) {
        return;
    }

    $main.owlCarousel({
        items: 1,
        singleItem: true,
        navigation: true,
        navigationText: ['<span>‹</span>', '<span>›</span>'],
        pagination: false,
        autoPlay: false,
        mouseDrag: true,
        touchDrag: true,
        afterAction: function (el) {
            var current = this.currentItem;
            $('.product-gallery-thumb').removeClass('is-active');
            $('.product-gallery-thumb[data-index="' + current + '"]').addClass('is-active');
        }
    });

    var owlInstance = $main.data('owlCarousel');

    $('.product-gallery-thumb').on('click', function (e) {
        e.preventDefault();
        var idx = parseInt($(this).attr('data-index'), 10);
        if (!isNaN(idx) && owlInstance) {
            owlInstance.goTo(idx);
        }
    });
})(window.jQuery);
