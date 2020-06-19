<div class="toast-wrapper" aria-live="polite" aria-atomic="true">
    <div class="toast-group">
        @if ($message = session('flash'))
            <div class="toast default" role="status" aria-live="polite" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ mix('/static/images/logo-32.png') }}" width="20" height="20" alt="">
                    <strong class="mr-auto">{{ getSiteConfig('name')}}</strong>

                    <button type="button" class="close" data-dismiss="toast" aria-label="{{ __('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="toast-body">
                    {{ $message }}
                </div>
            </div>
        @endif

        <div class="toast default reuse" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <img src="{{ mix('/static/images/logo-32.png') }}" width="20" height="20" alt="">
                <strong class="mr-auto">{{ getSiteConfig('name')}}</strong>

                <button type="button" class="close" aria-label="{{ __('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="toast-body"></div>
        </div>
    </div>
</div>
