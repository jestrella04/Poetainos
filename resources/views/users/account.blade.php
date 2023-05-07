@extends('layouts.index')

@section('meta.title', $params['title'])

@isset($params['canonical'])
    @section('link.canonical', $params['canonical'])
@endisset

@isset($user->extra_info['bio'])
    @section('meta.description', $user->extra_info['bio'])
@endisset

@isset($user->extra_info['interests'])
    @section('meta.keywords', $user->extra_info['interests'])
@endisset

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="users-main-content" class="main-content account-manager">
        <div class="d-flex mb-4 position-relative">
            <div>{!! getUserAvatar($user, $size = 'lg') !!}</div>
            <div class="flex-grow-1 px-3">
                <a href="{{ $user->path() }}" class="stretched-link">
                    <span class="text-bold">{{ $user->getName() }}</span><br>
                    <span class="text-muted">{{ '@' . $user->username }}</span>
                </a>
            </div>
        </div>

        <div class="mb-3">
            <div class="mb-1 smaller text-muted all-caps">
                {{ __('My account') }}
            </div>

            <nav class="nav flex-column">
                <a href="{{ route('users.edit', $user) }}" class="nav-link">{{ __('Update profile') }}</a>
                <a href="{{ $user->writingsPath() }}" class="nav-link">{{ __('View my writings') }}</a>
                <a href="{{ $user->shelfPath() }}" class="nav-link">{{ __('View my shelf') }}</a>
                <a href="{{ route('home') }}" class="nav-link disabled">{{ __('Manage blocked users') }}</a>
            </nav>
        </div>

        <div class="mb-3">
            <div class="mb-1 smaller text-muted all-caps">
                {{ __('Notifications') }}
            </div>

            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="enable-email-notifications">
                <label class="form-check-label" for="enable-email-notifications">{{ __('Email') }}</label>
            </div>

            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="enable-push-notifications">
                <label class="form-check-label" for="enable-push-notifications">{{ __('Push') }}</label>
            </div>
        </div>

        <div class="mb-3">
            <div class="mb-1 smaller text-muted all-caps">
                {{ __('Apariencia') }}
            </div>

                <button
                    type="button"
                    class="btn btn-link d-block"
                    data-bs-theme-value="light"
                    aria-pressed="false">
                    <i class="fas fa-sun fa-fw theme-icon" aria-hidden="true"></i>
                    {{ __('Light theme') }}
                </button>

                <button
                    type="button"
                    class="btn btn-link d-block"
                    data-bs-theme-value="dark"
                    aria-pressed="false">
                    <i class="fas fa-moon fa-fw theme-icon" aria-hidden="true"></i>
                    {{ __('Dark theme') }}
                </button>

                <button
                    type="button"
                    class="btn btn-link d-block"
                    data-bs-theme-value="auto"
                    aria-pressed="false">
                    <i class="fas fa-circle-half-stroke fa-fw theme-icon" aria-hidden="true"></i>
                    {{ __('Auto theme') }}
                </button>
        </div>

        <div class="mb-3">
            <div class="mb-1 smaller text-muted all-caps">
                {{ __('Danger zone') }}
            </div>

            <form id="user-logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
            </form>

            <form id="user-delete-form" class="d-inline" method="POST" action="{{ route('users.destroy', $user) }}">
                @csrf
                @method('delete')
            </form>

            <div class="d-grid gap-2">
                <button class="btn btn-primary btn-sm" form="user-logout-form" type="submit">{{ __('Logout') }}</button>
                <a class="btn btn-warning btn-sm mb-1"
                    data-bs-toggle="collapse"
                    href="#user-profile-delete"
                    role="button"
                    aria-expanded="false"
                    aria-controls="user-profile-delete">
                    {{ __('Delete account') }}
                </a>

                <div class="collapse" id="user-profile-delete">
                    <div class="card card-body d-grid gap-2">
                        <p>
                            {{ __("We're sorry to see you go") }}.
                            {{ __('Please be aware that when you delete your account all related content will also be removed') }},
                            {{ __('including but not limited to: writings, comments and replies, likes, shelved items, etc.') }}
                            <strong>{{ __('This process is permanent and undoable.') }}</strong>
                        </p>

                        <p>{{ __('Do you still want to delete your account?') }}</p>

                        <button
                            type="submit"
                            class="btn btn-danger btn-sm"
                            form="user-delete-form">
                            {{ __('Yes, delete the account') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection

