<div id="admin-types-form-wrapper" class="modal fade form-wrapper" data-backdrop="static" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title all-caps">
                    <span class="create">{{ __('New type') }}</span>
                    <span class="update d-none">{{ __('Edit type') }}</span>
                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="admin-types-form" action="{{ route('admin.types.edit') }}" method="POST">
                    @csrf
                    @method('put')

                    <input type="hidden" name="id" value="-1">

                    <div class="form-group">
                        <label for="name" class="col-form-label">{{ __('Name') }}:</label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control"
                            value=""
                            minlength="3"
                            maxlength="40"
                            placeholder=""
                            required>
                        <small id="name-error" class="text-danger d-none"></small>
                    </div>

                    <div class="form-group">
                        <label for="description" class="col-form-label">{{ __('Description') }}:</label>

                        <textarea
                            class="form-control"
                            name="description"
                            id="description"
                            rows="3"
                            minlength="3"
                            maxlength="255"
                            required></textarea>
                        <small id="description-error" class="text-danger d-none"></small>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="reset" id="reset" class="btn btn-danger" form="admin-types-form">{{ __('Reset') }}</button>
                <button type="submit" id="submit" class="btn btn-primary" form="admin-types-form">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
</div>
