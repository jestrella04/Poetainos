<div class="empty">
    <div class="title">
        @yield('empty-head', __('Nothing to display here'))
    </div>

    <p>
        @yield('empty-msg', __('Come back soon, new content is added frequently'))
    </p>

    <span class="empty-icon">
        <i class="fas fa-@yield('empty-icon', 'sad-tear')"></i>
    </span>
</div>
