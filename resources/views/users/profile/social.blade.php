@if (!empty($user->extra_info['website']))
<span class="flex-fill">
    <a href="{{ $user->extra_info['website'] }}" target="_blank" rel="noopener">
        <i class="fas fa-fw fa-globe" aria-hidden="true"></i>
    </a>
</span>
@endif

@if (!empty($user->extra_info['social']['twitter']))
<span class="flex-fill">
    <a href="{{ getSocialLink($user->extra_info['social']['twitter'], 'twitter') }}" target="_blank" rel="noopener">
        <i class="fab fa-fw fa-twitter" aria-hidden="true"></i>
    </a>
</span>
@endif

@if (!empty($user->extra_info['social']['instagram']))
<span class="flex-fill">
    <a href="{{ getSocialLink($user->extra_info['social']['instagram'], 'instagram') }}" target="_blank" rel="noopener">
        <i class="fab fa-fw fa-instagram" aria-hidden="true"></i>
    </a>
</span>
@endif

@if (!empty($user->extra_info['social']['facebook']))
<span class="flex-fill">
    <a href="{{ getSocialLink($user->extra_info['social']['facebook'], 'facebook') }}" target="_blank" rel="noopener">
        <i class="fab fa-fw fa-facebook" aria-hidden="true"></i>
    </a>
</span>
@endif

@if (!empty($user->extra_info['social']['youtube']))
<span class="flex-fill">
    <a href="{{ getSocialLink($user->extra_info['social']['youtube'], 'youtube') }}" target="_blank" rel="noopener">
        <i class="fab fa-fw fa-youtube" aria-hidden="true"></i>
    </a>
</span>
@endif

@if (!empty($user->extra_info['social']['goodreads']))
<span class="flex-fill">
    <a href="{{ getSocialLink($user->extra_info['social']['goodreads'], 'goodreads') }}" target="_blank" rel="noopener">
        <i class="fab fa-fw fa-goodreads-g" aria-hidden="true"></i>
    </a>
</span>
@endif
