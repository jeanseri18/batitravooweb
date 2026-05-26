<!-- Formater la date -->
@php
    $formatDate = function ($iso) {
        if (empty($iso)) return '—';
        try {
            $c = \Carbon\Carbon::parse($iso)->locale('fr');
            return $c->translatedFormat('d M Y, H:i');
        } catch (\Throwable) { return (string) $iso; }
    };
@endphp

<div class="app-card app-card--flush app-flex-between-wrap">
    <a href="{{ route('app.'.$profileSlug.'.support.create') }}" class="app-btn app-btn--inline">Nouveau ticket</a>
</div>

<div class="msn-tickets app-card app-mt">
    @if (! empty($supportList['data']) && count($supportList['data']))
        <ul class="msn-tickets__list" role="list">
            @foreach ($supportList['data'] as $t)
                <li>
                    <a href="{{ route('app.'.$profileSlug.'.support.show', ['ticket' => $t['id'] ?? 0]) }}" class="msn-ticket-row">
                        <span class="msn__avatar msn__avatar--ticket" aria-hidden="true">{{ $t['id'] ?? '—' }}</span>
                        <span class="msn-ticket-row__body">
                            <span class="msn-ticket-row__title">{{ $t['subject'] ?? 'Sans sujet' }}</span>
                            <span class="msn-ticket-row__meta">
                                <span class="msn__badge-status msn__badge-status--sm">{{ $t['status'] ?? '—' }}</span>
                                <span class="app-muted">{{ $t['priority'] ?? '—' }}</span>
                            </span>
                        </span>
                        <time class="msn-ticket-row__time">{{ $formatDate($t['updated_at'] ?? '') }}</time>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p class="app-muted msn-tickets__empty">Aucun ticket. Créez-en un pour contacter le support.</p>
    @endif
</div>
