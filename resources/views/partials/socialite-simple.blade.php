<div class="social">
    <a href="{{ route('social.login', 'facebook') }}"
        class="btn btn-primary"
        aria-label="{{ __('Continue with Facebook') }}"
        title="{{ __('Continue with Facebook') }}">
        <i class="fab fa-fw fa-facebook-f" aria-hidden="true"></i>
    </a>

    <a href="{{ route('social.login', 'twitter') }}"
        class="btn btn-primary"
        aria-label="{{ __('Continue with Twitter') }}"
        title="{{ __('Continue with Twitter') }}">
        <i class="fab fa-fw fa-twitter" aria-hidden="true"></i>
    </a>

    <a href="{{ route('social.login', 'google') }}"
        class="btn btn-primary"
        aria-label="{{ __('Continue with Google') }}"
        title="{{ __('Continue with Google') }}">
        <i class="fab fa-fw fa-google" aria-hidden="true"></i>
    </a>

    <a href="{{ route('login.email.check') }}"
        class="btn btn-primary"
        aria-label="{{ __('Continue with Email') }}"
        title="{{ __('Continue with Email') }}">
        <i class="fas fa-fw fa-at" aria-hidden="true"></i>
    </a>
</div>
