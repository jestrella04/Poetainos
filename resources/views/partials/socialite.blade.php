<a href="{{ route('social.login', 'facebook') }}"
    class="btn btn-primary btn-blue"
    title="{{ __('Login with Facebook') }}"
    data-toggle="tooltip"
    data-placement="top">
    <i class="fab fa-fw fa-facebook-f"></i>
</a>

<a href="{{ route('social.login', 'twitter') }}"
    class="btn btn-info"
    title="{{ __('Login with Twitter') }}"
    data-toggle="tooltip"
    data-placement="top">
    <i class="fab fa-fw fa-twitter"></i>
</a>

<a href="{{ route('social.login', 'google') }}"
    class="btn btn-danger"
    title="{{ __('Login with Google') }}"
    data-toggle="tooltip"
    data-placement="top">
    <i class="fab fa-fw fa-google"></i>
</a>
