(function () {
    'use strict';

    var header = document.querySelector('.site-header');
    var toggle = document.querySelector('.nav-toggle');
    var drawer = document.querySelector('.nav-drawer');

    if (header) {
        window.addEventListener('scroll', function () {
            header.classList.toggle('is-scrolled', window.scrollY > 12);
        }, { passive: true });
    }

    function closeDrawer() {
        if (!drawer || !toggle) return;
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    function openDrawer() {
        if (!drawer || !toggle) return;
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    if (toggle && drawer) {
        toggle.addEventListener('click', function () {
            if (drawer.classList.contains('is-open')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });

        drawer.addEventListener('click', function (e) {
            if (e.target === drawer) closeDrawer();
        });

        drawer.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeDrawer);
        });
    }

    var reveals = document.querySelectorAll('.section-reveal');
    if (reveals.length && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        reveals.forEach(function (el) { observer.observe(el); });
    } else {
        reveals.forEach(function (el) { el.classList.add('is-visible'); });
    }
})();
