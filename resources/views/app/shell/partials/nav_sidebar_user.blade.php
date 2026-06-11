@php
    $authUser = auth()->user();
    $slug = $profileSlug ?? '';
    $company = trim((string) ($authUser->company_name ?? ''));
    $useCompany = in_array($slug, ['batiment', 'fournisseur'], true) && $company !== '';
    $displayName = $useCompany ? $company : trim((string) ($authUser->name ?? 'Membre'));
    if ($displayName === '') {
        $displayName = 'Membre';
    }

    $initials = \Illuminate\Support\Str::of($displayName)
        ->trim()
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->join('');
    if ($initials === '') {
        $initials = '?';
    }

    $avatarUrl = $authUser->avatar_path ? storage_public_url($authUser->avatar_path) : null;
    $profileActive = in_array($page ?? '', ['profile', 'settings', 'profile_password', 'profile_location'], true);
    $roleLabel = match ($slug) {
        'particulier' => 'Particulier — client',
        'artisan' => 'Artisan',
        'batiment' => 'Entreprise BTP',
        'fournisseur' => 'Fournisseur matériaux',
        default => 'Membre',
    };
@endphp

<a href="{{ route('app.'.$slug.'.profile') }}"
   class="app-sidebar-user {{ $profileActive ? 'is-active' : '' }}"
   title="Mon profil">
    @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="" class="app-sidebar-user__avatar" width="52" height="52" loading="lazy">
    @else
        <span class="app-sidebar-user__avatar app-sidebar-user__avatar--initials" aria-hidden="true">{{ $initials }}</span>
    @endif
    <span class="app-sidebar-user__text">
        <span class="app-sidebar-user__name">{{ $displayName }}</span>
        <span class="app-sidebar-user__role">{{ $roleLabel }}</span>
    </span>
</a>
