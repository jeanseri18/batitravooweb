@php
    $notifUrl = route('app.'.$profileSlug.'.notifications');
    $unread = (int) ($notificationsFull['meta']['unread_count'] ?? 0);
    $rows = $notificationsFull['data'] ?? [];
@endphp

<div class="app-page-stack--split">
<div class="app-card app-notif-page-head">
    <div class="app-page-head app-page-head--flush">
        <div class="app-page-head__main">
            <h2 class="app-page-head__title">Notifications</h2>
            @if ($unread > 0)
                <p class="app-page-head__desc">{{ $unread }} notification{{ $unread > 1 ? 's' : '' }} non lue{{ $unread > 1 ? 's' : '' }}</p>
            @else
                <p class="app-page-head__desc">Tout est à jour</p>
            @endif
        </div>
        <div class="app-page-head__actions app-notif-page-head__actions">
        <form method="get" action="{{ $notifUrl }}" class="app-notif-page-head__per-page">
            <label for="notif_per_page" class="app-muted app-text-sm">Afficher</label>
            <select name="per_page" id="notif_per_page" class="mp-select" onchange="this.form.submit()">
                @foreach ([15, 30, 50] as $pp)
                    <option value="{{ $pp }}" @selected((int) request('per_page', 30) === $pp)>{{ $pp }}</option>
                @endforeach
            </select>
        </form>
        @if ($unread > 0)
            <form method="post" action="{{ route('app.'.$profileSlug.'.notifications.read_all') }}">
                @csrf
                <button type="submit" class="app-btn app-btn--secondary app-btn--sm app-btn--inline">Tout marquer comme lu</button>
            </form>
        @endif
        </div>
    </div>
</div>

<div class="app-card">
    @include('app.shell.partials.notifications_list', ['notifications' => $rows])
</div>
</div>
