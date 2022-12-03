<div class="modal fade" id="notifications-settings" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Notifications settings') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body">
                <p>
                    {{ __('If you haven\'t already, we highly recommend to enable push notifications.') }}
                    {{ __('These are real time notifications sent to every device you are logged in, so you ever miss anything.') }}
                </p>

                <div class="text-center">
                    <button class="btn btn-primary btn-push push-enable d-none" href="#">
                        <i class="fas fa-bell" aria-hidden="true"></i>
                        {{ __('Enable push notifications') }}
                    </button>

                    <button class="btn btn-danger btn-push push-disable d-none" href="#">
                        <i class="fas fa-bell-slash" aria-hidden="true"></i>
                        {{ __('Disable push notifications') }}
                    </button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
