<div id="admin-pages-form-wrapper" class="modal fade form-wrapper" data-backdrop="static" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title all-caps">{{ __('Create new page') }}</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="admin-pages-form" action="{{ route('admin.pages.edit') }}" method="POST">
                    @csrf
                    @method('put')

                    <input type="hidden" name="id" value="-1">

                    <div class="form-group row">
                        <label for="title" class="col-sm-2 col-form-label">{{ __('Title') }}</label>

                        <div class="col-sm-10">
                            <input
                                type="text"
                                name="title"
                                id="title"
                                class="form-control"
                                value=""
                                minlength="3"
                                maxlength="40"
                                placeholder=""
                                required
                                autofocus>
                            <small id="title-error" class="text-danger d-none"></small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="text" class="col-sm-2 col-form-label">{{ __('Text') }}</label>

                        <div class="col-sm-10">
                            <textarea
                                class="form-control"
                                name="text"
                                id="text"
                                rows="8"
                                minlength="10"
                                maxlength="5000"
                                required></textarea>
                            <small id="text-error" class="text-danger d-none"></small>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="reset" id="reset" class="btn btn-danger" form="admin-pages-form">{{ __('Reset') }}</button>
                <button type="submit" id="submit" class="btn btn-primary" form="admin-pages-form">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
</div>
