<div class="empty">
    <div class="title">
        @yield('empty-head', __('We can\'t show you this'))
    </div>

    <p>
        @yield('empty-msg', __('You have this writer blocked. If you have changed your mind, please proceed to unblock the writer.'))
    </p>

    <span class="empty-icon">
        <i class="fas fa-@yield('empty-icon', 'user-slash')" aria-hidden="true"></i>
    </span>
</div>
