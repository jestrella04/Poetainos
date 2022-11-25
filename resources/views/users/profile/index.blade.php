<div id="user-profile" class="card text-break">
    <div class="card-body">
        <div class="dropdown">
            <a class="btn btn-sm btn-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v fa-fw" aria-hidden="true"></i>
            </a>

            <ul class="dropdown-menu">
                <a href="#" class="dropdown-item share" data-wh-title="{{ $params['title'] }}"
                    data-wh-url="{{ $user->path() }}">
                    {{ __('Share profile') }}
                </a>
                @can('update', $user)
                    <a href="{{ route('users.edit', $user) }}" class="dropdown-item">
                        {{ __('Update profile') }}
                    </a>
                @endcan
                @if ($user->writings()->count() > 0)
                    <a href="{{ $user->writingsPath() }}" class="dropdown-item">
                        @if (auth()->check() &&
                            auth()->user()->is($user))
                            {{ __('View my writings') }}
                        @else
                            {{ __('View writings') }}
                        @endif
                    </a>
                @endif
                @if ($user->Shelf()->count() > 0)
                    <a href="{{ $user->shelfPath() }}" class="dropdown-item">
                        @if (auth()->check() &&
                            auth()->user()->is($user))
                            {{ __('View my shelf') }}
                        @else
                            {{ __('View shelf') }}
                        @endif
                    </a>
                @endif
                @if (auth()->check() &&
                    !auth()->user()->is($user))
                    <a href="{{ route('users.block.confirm', ['user' => $user->username]) }}"
                        class="dropdown-item init-block-user">
                        {{ __('Block user') }}
                    </a>
                @endif
            </ul>
        </div>

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

            <div class="d-flex">
                <div class="pe-3">
                    <i class="fas fa-calendar-check fa-fw" aria-hidden="true" title="{{ __('Registered') }}"></i>
                </div>

                <div class="flex-grow-1">{{ Carbon\Carbon::parse($user->created_at)->diffForHumans() }}</div>
            </div>


            @if (!empty($user->extra_info['location']))
                <div class="d-flex">
                    <div class="pe-3">
                        <i class="fas fa-fw fa-map-marker-alt" aria-hidden="true" title="{{ __('Location') }}"></i>
                    </div>

                    <div class="flex-grow-1">{{ $user->extra_info['location'] }}</div>
                </div>
            @endif

            @if (!empty($user->extra_info['occupation']))
                <div class="d-flex">
                    <div class="pe-3">
                        <i class="fas fa-fw fa-toolbox" aria-hidden="true" title="{{ __('Occupation') }}"></i>
                    </div>

                    <div class="flex-grow-1">{{ $user->extra_info['occupation'] }}</div>
                </div>
            @endif

            @if (!empty($user->extra_info['interests']))
                <div class="d-flex">
                    <div class="pe-3">
                        <i class="fas fa-fw fa-tv" aria-hidden="true" title="{{ __('Interests') }}"></i>
                    </div>

                    <div class="flex-grow-1">{{ $user->extra_info['interests'] }}</div>
                </div>
            @endif
        </div>

        @if ($user->writings()->count() > 0)
            <div class="d-flex justify-content-between smaller text-muted all-caps">
                <p>{{ __('Latest writings:') }}</p>
                <a href="{{ $user->writingsPath() }}" class="btn btn-sm text-muted">{{ __('View all') }}</a>
            </div>

            <div id="profile-writings" class="row p-2">
                @foreach ($user->writings()->take(2)->get() as $writing)
                    <div class="col-lg-6 profile-writing-entry">
                        <p class="h4 text-center">
                            <a href="{{ $writing->path() }}" class="stretched-link">{{ $writing->title }}</a>
                        </p>

                        <p class="text-center">
                            <i class="fas fa-calendar fa-fw" aria-hidden="true"></i>
                            {{ Carbon\Carbon::parse($writing->created_at)->diffForHumans() }}
                        </p>

                        <blockquote>
                            <div class="writing-read-more" data-link="{{ $writing->path() }}">
                                {!! nl2br(e($writing->excerpt())) !!}
                            </div>
                        </blockquote>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
