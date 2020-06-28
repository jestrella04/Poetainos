<div id="delete-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">{{ __('Please proceed with caution') }}...</span>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p class="confirm-msg">
                    {{ __('Are you sure you want to permanent delete this item?') }}
                    {{ __('This action is irreversible.') }}
                </p>

                <div class="alert alert-warning alert-delete d-none">
                    <small id="content-delete-warning"></small>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-danger"
                    data-dismiss="modal">
                    {{ __('No, cancel') }}
                </button>

                <button
                    id="btn-modal-delete"
                    type="button"
                    class="btn btn-primary"
                    data-dismiss="modal"
                    data-delete-url=""
                    data-redirect-url="">
                    {{ __("Yes, I'm sure") }}
                </button>
            </div>
        </div>
    </div>
</div>
