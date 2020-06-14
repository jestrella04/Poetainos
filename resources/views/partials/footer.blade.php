<footer id="footer">
    <div class="container">
        <div class="social-links">
            @if (getSiteConfig('social.twitter'))
                <a href="{{ getSocialLink(getSiteConfig('social.twitter'), 'twitter') }}}">
                    <i class="fab fa-twitter-square fa-fw"></i>
                </a>
            @endif

            @if (getSiteConfig('social.facebook'))
                <a href="{{ getSocialLink(getSiteConfig('social.facebook'), 'facebook') }}}">
                    <i class="fab fa-facebook-square fa-fw"></i>
                </a>
            @endif

            @if (getSiteConfig('social.instagram'))
                <a href="{{ getSocialLink(getSiteConfig('social.instagram'), 'instagram') }}}">
                    <i class="fab fa-instagram-square fa-fw"></i>
                </a>
            @endif

            @if (getSiteConfig('social.youtube'))
                <a href="{{ getSocialLink(getSiteConfig('social.youtube'), 'youtube') }}}">
                    <i class="fab fa-youtube-square fa-fw"></i>
                </a>
            @endif
        </div>

        <div class="copyright">
            &copy; 2020 <strong>{{ getSiteConfig('name') }}</strong>.
        </div>
    </div>

    <div id="back-to-top-wrapper" class="fade-out">
        <button id="back-to-top" class="btn btn-dark btn-sm">
            <i class="fas fa-chevron-up fa-fw"></i>
        </button>
    </div>
</footer>
