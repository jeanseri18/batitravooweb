@if ($profileSlug === 'fournisseur')
    @include('app.shell.partials.home_fournisseur')
@elseif ($profileSlug === 'batiment')
    @include('app.shell.partials.home_batiment')
@elseif ($profileSlug === 'particulier')
    @include('app.shell.partials.home_particulier')
@elseif ($profileSlug === 'artisan')
    @include('app.shell.partials.home_artisan')
@else
    @include('app.shell.partials.dashboard_metrics')
@endif

@if (! empty($notificationsPreview['data']))
    @php $previewUnread = (int) ($notificationsPreview['meta']['unread_count'] ?? 0); @endphp
    <section class="app-card app-home-section app-home-notif" aria-labelledby="home-notif-title">
        <div class="app-page-head app-page-head--flush">
            <div class="app-page-head__main">
                <h2 id="home-notif-title" class="app-page-head__title">Notifications récentes</h2>
            </div>
            @if ($previewUnread > 0)
                <div class="app-page-head__actions">
                    <span class="app-notif-page-head__badge">{{ $previewUnread }} non lue{{ $previewUnread > 1 ? 's' : '' }}</span>
                </div>
            @endif
        </div>
        @include('app.shell.partials.notifications_list', ['notifications' => $notificationsPreview['data']])
        <p class="app-home-notif__foot app-mb-0">
            <a href="{{ route('app.'.$profileSlug.'.notifications') }}" class="app-text-link">Voir toutes les notifications</a>
        </p>
    </section>
@endif
