<div id="main-toast" class="toast default" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
        <img src="{{ mix('/static/images/logo-32.png') }}" width="20" height="20" alt="">
        <strong class="mr-auto">{{ getSiteConfig('name')}}</strong>

        <button type="button" class="close" data-dismiss="toast" aria-label="{{ __('Close') }}">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="toast-body">
        <span class="d-none save-success">{{ __('Changes saved successfully') }}</span>
        <span class="d-none save-error">{{ __('It looks like something went wrong') }}</span>
        <span class="d-none delete-success">{{ __('Item deleted successfully') }}</span>
    </div>
</div>
