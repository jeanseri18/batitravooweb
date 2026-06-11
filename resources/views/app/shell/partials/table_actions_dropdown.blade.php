@php
    $actions = is_array($actions ?? null) ? $actions : [];
    $menuId = $menuId ?? ('table-actions-'.uniqid());
@endphp

@if ($actions === [])
    <span class="app-muted">—</span>
@else
    <div class="app-table-actions" data-table-actions>
        <button type="button"
                class="app-table-actions__toggle"
                aria-expanded="false"
                aria-haspopup="menu"
                aria-controls="{{ $menuId }}"
                id="{{ $menuId }}-btn">
            <span>Actions</span>
            <svg class="app-table-actions__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="app-table-actions__menu" id="{{ $menuId }}" role="menu" aria-labelledby="{{ $menuId }}-btn" hidden>
            @foreach ($actions as $action)
                @php
                    $type = (string) ($action['type'] ?? 'link');
                    $label = (string) ($action['label'] ?? '');
                    $danger = ! empty($action['danger']);
                    $hiddenFields = is_array($action['hidden'] ?? null) ? $action['hidden'] : [];
                @endphp
                @if ($label === '')
                    @continue
                @endif

                @if ($type === 'link')
                    <a href="{{ $action['href'] ?? '#' }}"
                       class="app-table-actions__item {{ $danger ? 'is-danger' : '' }}"
                       role="menuitem">{{ $label }}</a>
                @elseif ($type === 'form')
                    <form action="{{ $action['action'] ?? '#' }}"
                          method="{{ strtolower((string) ($action['method'] ?? 'post')) === 'get' ? 'get' : 'post' }}"
                          class="app-table-actions__form"
                          role="none"
                          @if (! empty($action['confirm'])) onsubmit="return confirm(@json($action['confirm']));" @endif>
                        @csrf
                        @if (! empty($action['method']) && strtoupper((string) $action['method']) !== 'POST')
                            @method($action['method'])
                        @endif
                        @foreach ($hiddenFields as $fname => $fval)
                            <input type="hidden" name="{{ $fname }}" value="{{ $fval }}">
                        @endforeach
                        <button type="submit"
                                class="app-table-actions__item {{ $danger ? 'is-danger' : '' }}"
                                role="menuitem">{{ $label }}</button>
                    </form>
                @endif
            @endforeach
        </div>
    </div>
@endif

@once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function closeAll(except) {
            document.querySelectorAll('[data-table-actions].is-open').forEach(function (wrap) {
                if (except && wrap === except) return;
                wrap.classList.remove('is-open');
                var btn = wrap.querySelector('.app-table-actions__toggle');
                var menu = wrap.querySelector('.app-table-actions__menu');
                if (btn) btn.setAttribute('aria-expanded', 'false');
                if (menu) menu.hidden = true;
            });
        }

        document.querySelectorAll('[data-table-actions]').forEach(function (wrap) {
            var btn = wrap.querySelector('.app-table-actions__toggle');
            var menu = wrap.querySelector('.app-table-actions__menu');
            if (!btn || !menu) return;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var open = wrap.classList.contains('is-open');
                closeAll();
                if (!open) {
                    wrap.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                    menu.hidden = false;
                }
            });
        });

        document.addEventListener('click', function () {
            closeAll();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAll();
        });
    });
    </script>
    @endpush
@endonce
