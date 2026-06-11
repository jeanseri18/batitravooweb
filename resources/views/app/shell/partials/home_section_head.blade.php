@php
    $sectionTitle = $title ?? '';
    $scrollTarget = $scrollTarget ?? null;
    $sectionId = $sectionId ?? ('home-section-'.uniqid());
@endphp

<div class="app-home-section-head">
    <h2 id="{{ $sectionId }}" class="app-home-section-head__title">{{ $sectionTitle }}</h2>
    @if ($scrollTarget)
        <div class="app-home-section-head__nav" role="group" aria-label="Navigation {{ $sectionTitle }}">
            <button type="button"
                    class="app-home-carousel-btn"
                    data-carousel-prev="{{ $scrollTarget }}"
                    aria-label="Voir les éléments précédents"
                    disabled>
                <span aria-hidden="true">‹</span>
            </button>
            <button type="button"
                    class="app-home-carousel-btn"
                    data-carousel-next="{{ $scrollTarget }}"
                    aria-label="Voir les éléments suivants">
                <span aria-hidden="true">›</span>
            </button>
        </div>
    @endif
</div>

@once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function carouselStep(el) {
            var first = el.querySelector(':scope > *:not([hidden])');
            if (!first) return Math.max(220, Math.floor(el.clientWidth * 0.72));
            var gap = parseFloat(getComputedStyle(el).columnGap || getComputedStyle(el).gap) || 12;
            return first.offsetWidth + gap;
        }

        function updateCarouselButtons(el) {
            if (!el || !el.id) return;
            var max = el.scrollWidth - el.clientWidth;
            var atStart = el.scrollLeft <= 2;
            var atEnd = el.scrollLeft >= max - 2;
            document.querySelectorAll('[data-carousel-prev="' + '#' + el.id + '"]').forEach(function (btn) {
                btn.disabled = atStart || max <= 0;
            });
            document.querySelectorAll('[data-carousel-next="' + '#' + el.id + '"]').forEach(function (btn) {
                btn.disabled = atEnd || max <= 0;
            });
        }

        function scrollCarousel(selector, dir) {
            var el = document.querySelector(selector);
            if (!el) return;
            el.scrollBy({ left: dir * carouselStep(el), behavior: 'smooth' });
        }

        document.querySelectorAll('[data-carousel-prev]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                scrollCarousel(btn.getAttribute('data-carousel-prev'), -1);
            });
        });
        document.querySelectorAll('[data-carousel-next]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                scrollCarousel(btn.getAttribute('data-carousel-next'), 1);
            });
        });

        document.querySelectorAll('.app-home-carousel').forEach(function (el) {
            updateCarouselButtons(el);
            el.addEventListener('scroll', function () {
                updateCarouselButtons(el);
            }, { passive: true });
            window.addEventListener('resize', function () {
                updateCarouselButtons(el);
            });
        });
    });
    </script>
    @endpush
@endonce
