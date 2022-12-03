<div class="card user-notification">
    <div class="card-body">
        <div class="d-flex justify-content-center">
            <div class="avatar-wrapper">
                @if (isset($notification->data['user_id']) && $user = App\Models\User::findOrFail($notification->data['user_id']))
                    {!! getUserAvatar($user, $size = 'lg') !!}
                @else
                    <img class="avatar avatar-lg" src="{{ Vite::asset('resources/images/logo.svg') }}" alt="{{ getSiteConfig('name') }}" loading="lazy">
                @endif
            </div>

            <div class="flex-grow-1">
                @if ($writing = App\Models\Writing::findOrFail($notification->data['writing_id']))
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
