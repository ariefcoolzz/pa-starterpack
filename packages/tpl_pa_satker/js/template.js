/**
 * Template pa_satker — skrip minimum, tanpa dependensi.
 * - tombol menu ponsel
 * - indikator slide hero (tanpa autoplay: hemat data)
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.querySelector('.pa-navtoggle');
        var nav = document.getElementById('pa-nav');

        if (toggle && nav) {
            toggle.addEventListener('click', function () {
                var open = nav.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }

        // Sorot titik hero sesuai slide yang terlihat (bila ada).
        var slides = document.querySelector('.pa-hero__slides');
        var dots = document.querySelectorAll('.pa-hero__dots a');

        if (slides && dots.length) {
            slides.addEventListener('scroll', function () {
                var index = Math.round(slides.scrollLeft / slides.clientWidth);
                dots.forEach(function (dot, i) {
                    dot.style.background = i === index ? 'var(--pa-primary)' : 'var(--pa-line)';
                });
            }, { passive: true });
        }
    });
})();
