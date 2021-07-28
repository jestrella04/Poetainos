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
                                <a class="dropdown-item btn-push push-enable d-none" href="#">
                                    <i class="fas fa-bell" aria-hidden="true"></i>
                                    {{ __('Enable push notifications') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item btn-push push-disable d-none" href="#">
                                    <i class="fas fa-bell-slash" aria-hidden="true"></i>
                                    {{ __('Disable push notifications') }}
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

            @forelse ($notifications as $notification)
                <div class="card user-notification">
                    <div class="card-body">
                        <div class="d-flex justify-content-center">
                            <div class="avatar-wrapper">
                                @if (isset($notification->data['user_id']) && $user = App\User::findOrFail($notification->data['user_id']))
                                    {!! getUserAvatar($user, $size = 'lg') !!}
                                @else
                                    <img class="avatar avatar-lg" src="{{ mix('/static/images/logo.svg') }}" alt="{{ getSiteConfig('name') }}" loading="lazy">
                                @endif
                            </div>

                            <div class="flex-grow-1">
                                @if ($writing = App\Writing::findOrFail($notification->data['writing_id']))
                                    <a href="{{ route('notifications.read', $notification) }}" class="stretched-link">
                                        {{ $writing->title }}
                                    </a>
                                @endif

                                <div>{{ getNotificationMessage($notification) }}.</div>

                                <small class="all-caps text-muted">
                                    {{ Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                @section('empty-head', '')
                @section('empty-msg', __('You have no notifications at this time'))
                @section('empty-icon', 'bell')
                @include('partials.empty')
            @endforelse
        </div>
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection

