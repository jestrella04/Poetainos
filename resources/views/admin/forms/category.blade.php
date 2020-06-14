<div id="admin-categories-form-wrapper" class="modal fade form-wrapper" data-backdrop="static" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title all-caps">{{ __('Create new category') }}</h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="admin-categories-form" action="{{ route('admin.categories.update') }}" method="POST">
                    @csrf
                    @method('put')

                    <input type="hidden" name="id" value="-1">

                    <div class="alert alert-success d-none" role="alert">
                        <button type="button" class="close">
                            <span class="alert-link">&times;</span>
                        </button>

                        <h6 class="alert-heading">
                            {{ __('Changes saved successfully') }}
                        </h6>
                    </div>

                    <div class="alert alert-danger d-none" role="alert">
                        <button type="button" class="close">
                            <span class="danger-link">&times;</span>
                        </button>

                        <h6 class="alert-heading">
                            {{ __('It looks like something went wrong') }}
                        </h6>
                    </div>

                    <div class="form-group row">
                        <label for="title" class="col-sm-2 col-form-label">{{ __('Name') }}</label>

                        <div class="col-sm-10">
                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control"
                                value=""
                                minlength="3"
                                maxlength="40"
                                placeholder=""
                                required
                                autofocus>
                            <small id="name-error" class="text-danger d-none"></small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="description" class="col-sm-2 col-form-label">{{ __('Description') }}</label>

                        <div class="col-sm-10">
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
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="reset" id="reset" class="btn btn-danger" form="admin-categories-form">{{ __('Reset') }}</button>
                <button type="submit" id="submit" class="btn btn-primary" form="admin-categories-form">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
</div>
