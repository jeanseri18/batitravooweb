@extends('app.layouts.shell')

@section('content')
    @if (! empty($apiError))
        <div class="app-alert app-alert--error" role="alert">{{ $apiError }}</div>
    @endif

    <div class="app-mockup-page" data-mockup-page="{{ $page ?? '' }}">
        @include('app.shell.partials.'.$page)
    </div>
@endsection
