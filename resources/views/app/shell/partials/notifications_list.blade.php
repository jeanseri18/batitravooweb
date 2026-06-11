@php
    $rows = $notifications ?? [];
    if (! is_array($rows)) {
        $rows = [];
    }

    $notifKind = static function (?string $type): string {
        $t = mb_strtolower(trim((string) $type));
        if ($t === 'message') {
            return 'message';
        }
        if (in_array($t, ['devis', 'candidature'], true)) {
            return 'success';
        }

        return 'system';
    };

    $notifIcon = static function (string $kind): string {
        return match ($kind) {
            'message' => 'chat',
            'success' => 'document',
            default => 'bell',
        };
    };

    $relativeTimeFr = static function (?string $iso): string {
        if (empty($iso)) {
            return '';
        }
        try {
            $dt = \Carbon\Carbon::parse($iso)->locale('fr');
            if ($dt->diffInDays(now()) > 6) {
                return $dt->translatedFormat('d M Y');
            }

            return $dt->diffForHumans();
        } catch (\Throwable) {
            return '';
        }
    };
@endphp

@if (count($rows) > 0)
    <ul class="app-notif-feed" role="list">
        @foreach ($rows as $n)
            @if (! is_array($n))
                @continue
            @endif
            @php
                $kind = $notifKind($n['type'] ?? null);
                $isUnread = empty($n['read']);
                $timeLabel = $relativeTimeFr($n['created_at'] ?? null);
            @endphp
            <li class="app-notif-card {{ $isUnread ? 'is-unread' : 'is-read' }}">
                <div class="app-notif-card__icon app-notif-card__icon--{{ $kind }}" aria-hidden="true">
                    @include('app.partials.app-nav-icon', ['name' => $notifIcon($kind)])
                </div>
                <div class="app-notif-card__body">
                    <div class="app-notif-card__head">
                        <strong class="app-notif-card__title">{{ $n['title'] ?? '—' }}</strong>
                        @if ($timeLabel !== '')
                            <time class="app-notif-card__time" datetime="{{ $n['created_at'] ?? '' }}">{{ $timeLabel }}</time>
                        @endif
                    </div>
                    @if (! empty($n['body']))
                        <p class="app-notif-card__text">{{ $n['body'] }}</p>
                    @endif
                </div>
                @if ($isUnread)
                    <span class="app-notif-card__dot" aria-label="Non lue"></span>
                @endif
            </li>
        @endforeach
    </ul>
@else
    <div class="app-notif-empty">
        <span class="app-notif-empty__icon" aria-hidden="true">@include('app.partials.app-nav-icon', ['name' => 'bell'])</span>
        <p class="app-notif-empty__title">Aucune notification</p>
        <p class="app-notif-empty__hint">Les alertes liées à vos devis, commandes et messages apparaîtront ici.</p>
    </div>
@endif
