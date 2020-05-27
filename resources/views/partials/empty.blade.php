<div class="empty">
    <h4 class="text-muted">
        @yield('empty-head', __('Nothing to display here'))
    </h4>

    <p class="text-muted">
        @yield('empty-msg', __('Come back soon, new content is added frequently'))
    </p>

    <span class="empty-icon">
        <i class="fas fa-@yield('empty-icon', 'sad-tear')"></i>
    </span>
</div>
