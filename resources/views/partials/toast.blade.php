<div class="toast-wrapper" aria-live="polite" aria-atomic="true">
    <div class="toasts">
        @if ($message = session('flash'))
            <div id="toast-flash" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ mix('/static/images/logo.svg') }}" width="20" height="20" alt="">
                    <strong class="me-auto">{{ getSiteConfig('name')}}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
                </div>

                <div class="toast-body">
                    {{ $message }}
                </div>
            </div>
        @endif

        <div id="toast-reuse" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <img src="{{ mix('/static/images/logo.svg') }}" width="20" height="20" alt="">
                <strong class="me-auto">{{ getSiteConfig('name')}}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="toast-body"></div>
        </div>
    </div>
</div>
