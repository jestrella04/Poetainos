<div class="social d-grid gap-2 mx-auto">
    <a href="{{ route('social.login', 'facebook') }}"
        class="btn btn-primary">
        <i class="fab fa-fw fa-facebook-f" aria-hidden="true"></i>
        {{ __('Continue with Facebook') }}
    </a>

    <a href="{{ route('social.login', 'twitter') }}"
        class="btn btn-primary">
        <i class="fab fa-fw fa-twitter" aria-hidden="true"></i>
        {{ __('Continue with Twitter') }}
    </a>

    <a href="{{ route('social.login', 'google') }}"
        class="btn btn-primary">
        <i class="fab fa-fw fa-google" aria-hidden="true"></i>
        {{ __('Continue with Google') }}
    </a>

    <a href="{{ route('login') }}"
        class="btn btn-primary">
        <i class="fas fa-fw fa-at" aria-hidden="true"></i>
        {{ __('Continue with Email') }}
    </a>

    <a href="{{ route('home') }}"
        class="btn btn-primary">
        <i class="fas fa-fw fa-ghost" aria-hidden="true"></i>
        {{ __('Continue as a guest') }}
    </a>
</div>
