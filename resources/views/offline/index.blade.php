@extends('layouts.master')

@section('meta.title', $params['title'])

@section('body')
<main role="main" class="offline-wrapper">
    <div id="offline">
        <img src="{{ mix('/static/images/logo.svg') }}">
        <h1>{{ getSiteConfig('name') }}</h1>
        <p class="lead">{{ __('Oh no, it seems you are offline!') }}</p>

        <div class="my-5">
            <i class="fas fa-sad-tear fa-7x" aria-hidden="true"></i>
        </div>

        <div class="d-grid gap-2 mb-3">
            <a class="btn btn-primary btn-lg"
                href="#"
                onclick="location.reload()"
                role="button">{{ __('Reload') }}</a>
        </div>
    </div>
</main>
@endsection
