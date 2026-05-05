(function () {
    'use strict';

    var input = document.getElementById('search-form');
    if (!input) { return; }

    // Pre-fill from URL ?q= on the results page
    var urlQ = new URLSearchParams(window.location.search).get('q');
    if (urlQ && input.value === '') { input.value = urlQ; }

    var dropdown = document.createElement('div');
    dropdown.id = 'search-dropdown';
    input.parentNode.appendChild(dropdown);

    var timer = null;
    var lastTerm = '';

    function hide() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
    }

    function show(items) {
        dropdown.innerHTML = '';
        if (!items.length) { hide(); return; }
        items.forEach(function (item) {
            var a = document.createElement('a');
            a.className = 'search-result-item' + (item.id === '#' ? ' no-results' : '');
            a.textContent = item.label;
            if (item.id === '#') {
                a.href = '#';
                a.addEventListener('click', function (e) { e.preventDefault(); });
            } else {
                a.href = item.id;
            }
            dropdown.appendChild(a);
        });
        dropdown.style.display = 'block';
    }

    input.addEventListener('input', function () {
        var term = input.value.trim();
        if (term.length < 2) { hide(); return; }
        if (term === lastTerm) { return; }
        lastTerm = term;
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch('/search?term=' + encodeURIComponent(term))
                .then(function (r) { return r.json(); })
                .then(show)
                .catch(hide);
        }, 250);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { hide(); input.blur(); }
    });

    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            hide();
        }
    });
})();
