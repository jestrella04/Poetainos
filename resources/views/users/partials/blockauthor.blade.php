<div class="modal fade modal-complaint"
    {{-- id="{{ $params['modal_id'] }}" --}}
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    tabindex="-1"
    aria-labelledby=""
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <p class="modal-title">{{ __('Block writer') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body">
                <p>
                    {{ __('If you block a user you will not be able to see any of their writings or comments.') }}
                    {{ __('Are you sure you want to block :name?', ['name' => $user->getName() ]) }}
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary block-user-submit" data-href="{{ route('users.block.confirmed', ['user' => $user]) }}">{{ __('Block') }}</button>
            </div>
        </div>
    </div>
</div>
