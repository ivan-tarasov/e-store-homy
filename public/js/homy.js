(function () {
    'use strict';

    var backTop = document.getElementById('back-top');
    if (!backTop) {
        return;
    }

    backTop.style.display = 'none';

    window.addEventListener('scroll', function () {
        backTop.style.display = window.scrollY > 400 ? 'block' : 'none';
    });

    backTop.addEventListener('click', function (e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
