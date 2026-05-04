(function () {
    'use strict';

    if (typeof window.Swiper !== 'function') {
        return;
    }

    var main = document.getElementById('product-gallery-main');
    if (!main) {
        return;
    }

    var thumbs = Array.prototype.slice.call(
        document.querySelectorAll('.product-gallery-thumb')
    );

    function setActiveThumb(idx) {
        thumbs.forEach(function (el) {
            var i = parseInt(el.getAttribute('data-index'), 10);
            el.classList.toggle('is-active', i === idx);
        });
    }

    var swiper = new window.Swiper(main, {
        slidesPerView: 1,
        navigation: {
            nextEl: '#product-gallery-main .swiper-button-next',
            prevEl: '#product-gallery-main .swiper-button-prev',
        },
        on: {
            slideChange: function () {
                setActiveThumb(this.activeIndex);
            },
        },
    });

    thumbs.forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            var idx = parseInt(el.getAttribute('data-index'), 10);
            if (!isNaN(idx)) {
                swiper.slideTo(idx);
            }
        });
    });
})();
