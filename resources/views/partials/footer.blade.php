<footer id="footer">
    <div class="container">
        <div class="social-links">
            @if (getSiteConfig('social.twitter'))
                <a href="{{ getSocialLink(getSiteConfig('social.twitter'), 'twitter') }}"
                    title="{{ __('Follow us on Twitter') }}"
                    aria-label="{{ __('Follow us on Twitter') }}"
                    data-bs-toggle="tooltip">
                    <i class="fab fa-twitter fa-fw" aria-hidden="true"></i>
                </a>
            @endif

            @if (getSiteConfig('social.facebook'))
                <a href="{{ getSocialLink(getSiteConfig('social.facebook'), 'facebook') }}"
                    title="{{ __('Join us on Facebook') }}"
                    aria-label="{{ __('Join us on Facebook') }}"
                    data-bs-toggle="tooltip">
                    <i class="fab fa-facebook fa-fw" aria-hidden="true"></i>
                </a>
            @endif

            @if (getSiteConfig('social.instagram'))
                <a href="{{ getSocialLink(getSiteConfig('social.instagram'), 'instagram') }}"
                    title="{{ __('Follow us on Instagram') }}"
                    aria-label="{{ __('Follow us on Instagram') }}"
                    data-bs-toggle="tooltip">
                    <i class="fab fa-instagram fa-fw" aria-hidden="true"></i>
                </a>
            @endif

            @if (getSiteConfig('social.youtube'))
                <a href="{{ getSocialLink(getSiteConfig('social.youtube'), 'youtube') }}"
                    title="{{ __('Join us on Youtube') }}"
                    aria-label="{{ __('Join us on Youtube') }}"
                    data-bs-toggle="tooltip">
                    <i class="fab fa-youtube fa-fw" aria-hidden="true"></i>
                </a>
            @endif

            @if (getSiteConfig('social.telegram'))
                <a href="{{ getSocialLink(getSiteConfig('social.telegram'), 'telegram') }}"
                    title="{{ __('Join our Telegram channel') }}"
                    aria-label="{{ __('Join our Telegram channel') }}"
                    data-bs-toggle="tooltip">
                    <i class="fab fa-telegram-plane fa-fw" aria-hidden="true"></i>
                </a>
            @endif
        </div>

        <div class="copyright">
            &copy; 2020 <strong>{{ getSiteConfig('name') }}</strong>.
        </div>

        <div class="pages">
            @foreach (App\Page::all() as $page)
                <a href="{{ route('pages.show', $page->slug ) }}">{{ $page->title }}</a>
                <span class="separator">|</span>
            @endforeach

            <a href="{{ route('contact.create') }}">{{ __('Contact form') }}</a>
            <span class="separator">|</span>
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
