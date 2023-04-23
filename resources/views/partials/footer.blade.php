@if (Route::current()->getName() !== 'writings.create')
    <a href="{{ route('writings.create') }}"
        class="btn btn-primary btn-fixed-br"
        aria-label="{{ __('Publish') }}">
        <i class="fas fa-pen-nib fa-fw" aria-hidden="true"></i>
    </a>
@endif

<footer id="footer" class="bg-body-tertiary">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-md-between">
            <div class="copyright footer-column">
                <p class="lead">{{ getSiteConfig('name') }}</p>

                <span>
                    &copy; 2020<br>
                    {{ __('All rights reserved.') }}
                </span>
            </div>

            <div class="pages footer-column">
                <p class="column-title">{{ __('Links') }}</p>

                <div class="nav flex-column">
                    @foreach (App\Models\Page::all() as $page)
                    <a class="nav-link" href="{{ route('pages.show', $page->slug ) }}">{{ $page->title }}</a>
                    @endforeach
                    <a class="nav-link" href="{{ route('contact.create') }}" rel="noindex">{{ __('Contact us') }}</a>
                </div>
            </div>

            <div class="apps footer-column">
                <p class="column-title">{{ __('Get the app') }}</p>

                <div class="d-flex flex-column gap-2">
                    @foreach (getSiteConfig('stores') as $store => $info)
                        @if (! empty($info['value']))
                    <div>
                        <a class="btn btn-primary btn-sm text-nowrap" href="{{ $info['value'] }}" target="_blank" rel="noopener">
                            <i class="{{ $info['icon'] }} fa-fw" aria-hidden="true"></i>
                            {{ $store }}
                        </a>
                    </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="social-links footer-column">
                <p class="column-title">{{ __('Follow us at') }}</p>

                <div class="nav flex-column">
                    @foreach (getSiteConfig('social') as $site => $info)
                        @if (! empty($info['value']))
                    <a class="nav-link"
                        href="{{ getSocialLink($info['value'], $site) }}"
                        target="blank"
                        rel="noopener">
                        <i class="fab fa-{{ $site }} fa-fw" aria-hidden="true"></i>
                        {{ ucfirst($site) }}
                    </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</footer>
