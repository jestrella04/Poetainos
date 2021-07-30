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

                <nav class="nav flex-column">
                    @foreach (App\Page::all() as $page)
                    <a class="nav-link" href="{{ route('pages.show', $page->slug ) }}">{{ $page->title }}</a>
                    @endforeach
                    <a class="nav-link" href="{{ route('contact.create') }}">{{ __('Contact form') }}</a>
                </nav>
            </div>

            <div class="apps footer-column">
                <p class="column-title">{{ __('Get the app') }}</p>

                <div class="d-flex flex-column gap-2">
                    <div>
                        <a class="btn btn-primary" href="https://play.google.com/store/apps/details?id=com.poetainos.twa" target="_blank" rel="noopener">
                            <i class="fab fa-google-play fa-fw" aria-hidden="true"></i>
                            Google Play
                        </a>
                    </div>

                    {{-- <div>
                        <a class="btn btn-primary" href="http://" target="_blank" rel="noopener">
                            <i class="fab fa-microsoft fa-fw" aria-hidden="true"></i>
                            Microsoft Store
                        </a>
                    </div> --}}
                </div>
            </div>

            <div class="social-links footer-column">
                <p class="column-title">{{ __('Follow us at') }}</p>

                <nav class="nav flex-column">
                    @foreach (getSiteConfig('social') as $site => $info)
                    <a class="nav-link"
                        href="{{ getSocialLink($info['value'], $site) }}"
                        target="blank"
                        rel="noopener">
                        <i class="fab fa-{{ $site }} fa-fw" aria-hidden="true"></i>
                        {{ ucfirst($site) }}
                    </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>

    <div id="back-to-top-wrapper" class="fade-out">
        <button
            id="back-to-top"
            class="btn btn-primary"
            title="{{ __('Back to top') }}"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            aria-label="{{ __('Back to top') }}">
            <i class="fas fa-chevron-up" aria-hidden="true"></i>
        </button>
    </div>
</footer>
