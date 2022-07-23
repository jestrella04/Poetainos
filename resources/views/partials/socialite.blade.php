<div class="social d-grid gap-2 mx-auto">
    <a href="{{ route('social.login', 'facebook') }}"
        class="btn btn-primary d-flex">
        <span class="col-2 col-lg-4">
            <i class="fab fa-fw fa-facebook-f" aria-hidden="true"></i>
        </span>

        <span class="flex-grow-1 text-lg-start">
            {{ __('Continue with Facebook') }}
        </span>
    </a>

    <a href="{{ route('social.login', 'twitter') }}"
        class="btn btn-primary d-flex">
        <span class="col-2 col-lg-4">
            <i class="fab fa-fw fa-twitter" aria-hidden="true"></i>
        </span>

        <span class="flex-grow-1 text-lg-start">
            {{ __('Continue with Twitter') }}
        </span>
    </a>

    <a href="{{ route('social.login', 'google') }}"
        class="btn btn-primary d-flex">
        <span class="col-2 col-lg-4">
            <i class="fab fa-fw fa-google" aria-hidden="true"></i>
        </span>

        <span class="flex-grow-1 text-lg-start">
            {{ __('Continue with Google') }}
        </span>
    </a>

    <a href="{{ route('login') }}"
        class="btn btn-primary d-flex">
        <span class="col-2 col-lg-4">
            <i class="fas fa-fw fa-at" aria-hidden="true"></i>
        </span>

        <span class="flex-grow-1 text-lg-start">
            {{ __('Continue with Email') }}
        </span>
    </a>

    <a href="{{ route('home') }}"
        class="btn btn-primary d-flex">
        <span class="col-2 col-lg-4">
            <i class="fas fa-fw fa-ghost" aria-hidden="true"></i>
        </span>

        <span class="flex-grow-1 text-lg-start">
            {{ __('Continue as a guest') }}
        </span>
    </a>
</div>
