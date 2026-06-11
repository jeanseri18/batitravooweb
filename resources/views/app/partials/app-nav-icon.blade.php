@php
    $safe = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($name ?? '')));
    $id = 'app-nav-ico-'.($safe !== '' ? $safe : 'home');
@endphp
<span class="app-nav-ico-wrap" aria-hidden="true">
    <svg class="app-nav-ico" width="20" height="20" focusable="false"><use href="#{{ $id }}" xlink:href="#{{ $id }}"/></svg>
</span>
