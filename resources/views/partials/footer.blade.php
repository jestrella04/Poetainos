<application-footer class="d-none">
    <div class="container">
        <p class="fw-lighter fs-6 text-center">
            {{ getSiteConfig('name') }}
            &copy; 2020. {{ __('All rights reserved.') }}
        </p>
    </div>
</application-footer>

<footer id="footer">
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
                    @foreach (App\Page::all() as $page)
                    <a class="nav-link" href="{{ route('pages.show', $page->slug ) }}">{{ $page->title }}</a>
                    @endforeach
                    <a class="nav-link" href="{{ route('contact.create') }}">{{ __('Contact form') }}</a>
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

    <div id="back-to-top-wrapper" class="fade-out">
        <button
            id="back-to-top"
            class="btn btn-primary jump-to-nav"
            title="{{ __('Back to top') }}"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            aria-label="{{ __('Back to top') }}">
            <i class="fas fa-chevron-up" aria-hidden="true"></i>
        </button>
    </div>
</footer>
