<div id="user-profile" class="card text-break">
    <div class="card-body">
        @include('users.profile.dropdown')

        <div id="profile-header" class="text-center">
            {!! getUserAvatar($user, $size = 'xxl', $classList = ['mx-auto']) !!}

            <span class="user-name">{{ $user->getName() }}</span>
            <span class="username text-muted">{{ '@' . $user->username }}</span>

            @if (!empty($user->extra_info['bio']))
                <div class="profile-bio smaller lead">{{ $user->extra_info['bio'] }}</div>
            @endif
        </div>

        <div id="profile-social-links" class="d-flex">
            @include('users.profile.social')
        </div>

        <div id="profile-stats">
            @include('users.profile.stats')
        </div>

        <div id="profile-more-info">
            <p class="smaller text-muted all-caps">{{ __('More information:') }}</p>

            <div class="row">
                <div class="col-sm-4">
                    <i class="fas fa-calendar-check fa-fw" aria-hidden="true"></i>
                    {{ __('Registered') }}
                </div>

                <div class="col-sm-8">{{ Carbon\Carbon::parse($user->created_at)->diffForHumans() }}</div>
            </div>

            <div class="row">
                @if (!empty($user->extra_info['location']))
                    <div class="col-sm-4">
                        <i class="fas fa-fw fa-map-marker-alt" aria-hidden="true"></i>
                        {{ __('Location') }}
                    </div>

                    <div class="col-sm-8">{{ $user->extra_info['location'] }}</div>
                @endif
            </div>

            <div class="row">
                @if (!empty($user->extra_info['occupation']))
                    <div class="col-sm-4">
                        <i class="fas fa-fw fa-toolbox" aria-hidden="true"></i>
                        {{ __('Occupation') }}
                    </div>

                    <div class="col-sm-8">{{ $user->extra_info['occupation'] }}</div>
                @endif
            </div>

            <div class="row">
                @if (!empty($user->extra_info['interests']))
                    <div class="col-sm-4">
                        <i class="fas fa-fw fa-tv" aria-hidden="true"></i>
                        {{ __('Interests') }}
                    </div>

                    <div class="col-sm-8">{{ $user->extra_info['interests'] }}</div>
                @endif
            </div>
        </div>

        @if ($user->writings()->count() > 0)
            <div class="smaller text-muted all-caps text-center">
                {{ __('Latest writings:') }}
            </div>

            <div id="profile-writings" class="row p-2">
                @foreach ($user->writings()->latest()->take(2)->get() as $writing)
                    <div class="col-lg-6 profile-writing-entry">
                        <p class="h4 writing-title">
                            <a href="{{ $writing->path() }}" class="stretched-link">{{ $writing->title }}</a>
                        </p>

                        <p class="writing-subtitle">
                            <i class="fas fa-calendar fa-fw" aria-hidden="true"></i>
                            {{ Carbon\Carbon::parse($writing->created_at)->diffForHumans() }}
                        </p>

                        <blockquote>
                            {!! nl2br(e($writing->excerpt())) !!}
                        </blockquote>
                    </div>
                @endforeach
            </div>

            <div class="text-center">
                <a href="{{ $user->writingsPath() }}" class="btn btn-secondary btn-sm">{{ __('View all') }}</a>
            </div>
        @endif
    </div>
</div>
