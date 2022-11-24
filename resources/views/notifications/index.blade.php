@extends('layouts.index')

@section('meta.title', $params['title'])

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
    <div id="notifications-main-content" class="main-content">
        @include('notifications.partials.settings')
        @include('notifications.partials.toplinks')

        <div class="user-notifications">
            <div class="d-flex flex-wrap">
                <div class="flex-grow-1">
                    <h2 class="all-caps">{{ __('Notifications') }}</h2>
                </div>

                <form action="{{ route('notifications.clear') }}" method="post" name="notifications.clear">
                    @csrf
                    <div class="dropdown">
                        <button
                            class="btn btn-light"
                            type="button"
                            data-bs-toggle="dropdown"
                            role="menu"
                            aria-label="{{ __('Notification actions') }}"
                            aria-expanded="false">
                            <i class="fas fa-ellipsis-v" aria-hidden="true"></i>
                        </button>

                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#notifications-settings">
                                    <i class="fas fa-sliders-h" aria-hidden="true"></i>
                                    {{ __('Notifications settings') }}
                                </a>
                            </li>

                            <li>
                                <button class="dropdown-item" type="submit">
                                    <i class="fas fa-check-double" aria-hidden="true"></i>
                                    {{ __('Mark all as read') }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </form>
            </div>

            <div class="notifications-list infinite-scroll">
            @forelse ($notifications as $notification)
                @include('notifications.show')
            @empty
                @section('empty-head', '')
                @section('empty-msg', __('You have no notifications at this time'))
                @section('empty-icon', 'bell')
                @include('partials.empty')
            @endforelse
            </div>

            <div id="notifications-pagination" class="mt-4">
                {{ $notifications->links()}}
            </div>

            @if ($notifications->hasMorePages())
                @include('partials.loading')
            @endif
        </div>
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection

