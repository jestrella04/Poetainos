<footer id="footer">
    <div class="container">
        <div class="social-links">
            @if (getSiteConfig('social.twitter'))
                <a href="{{ getSocialLink(getSiteConfig('social.twitter'), 'twitter') }}"
                    title="{{ __('Follow us on Twitter') }}"
                    aria-label="{{ __('Follow us on Twitter') }}"
                    data-toggle="tooltip">
                    <i class="fab fa-twitter fa-fw"></i>
                </a>
            @endif

            @if (getSiteConfig('social.facebook'))
                <a href="{{ getSocialLink(getSiteConfig('social.facebook'), 'facebook') }}"
                    title="{{ __('Join us on Facebook') }}"
                    aria-label="{{ __('Join us on Facebook') }}"
                    data-toggle="tooltip">
                    <i class="fab fa-facebook fa-fw"></i>
                </a>
            @endif

            @if (getSiteConfig('social.instagram'))
                <a href="{{ getSocialLink(getSiteConfig('social.instagram'), 'instagram') }}"
                    title="{{ __('Follow us on Instagram') }}"
                    aria-label="{{ __('Follow us on Instagram') }}"
                    data-toggle="tooltip">
                    <i class="fab fa-instagram fa-fw"></i>
                </a>
            @endif

            @if (getSiteConfig('social.youtube'))
                <a href="{{ getSocialLink(getSiteConfig('social.youtube'), 'youtube') }}"
                    title="{{ __('Join us on Youtube') }}"
                    aria-label="{{ __('Join us on Youtube') }}"
                    data-toggle="tooltip">
                    <i class="fab fa-youtube fa-fw"></i>
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
        </div>
    </div>

    <div id="back-to-top-wrapper" class="fade-out">
        <button
            id="back-to-top"
            class="btn btn-primary"
            title="{{ __('Back to top') }}"
            data-toggle="tooltip"
            data-placement="left"
            aria-label="{{ __('Back to top') }}">
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>
</footer>
