<div id="admin-categories-form-wrapper" class="modal fade form-wrapper" data-bs-backdrop="static" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title all-caps">
                    <span class="create">{{ __('New category') }}</span>
                    <span class="update d-none">{{ __('Edit category') }}</span>
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body">
                <form id="admin-categories-form" action="{{ route('admin.categories.edit') }}" method="POST">
                    @csrf
                    @method('put')

                    <input type="hidden" name="id" value="-1">

                    <div class="mb-3">
                        <label for="title" class="col-form-label">{{ __('Name') }}:</label>

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
                            autocomplete="off">
                        <small id="name-error" class="text-danger d-none"></small>
                    </div>

                    @if ($categories->count() > 0)
                        <div class="mb-3">
                            <label for="parent" class="col-form-label">{{ __('Parent') }}:</label>

                            <select
                                name="parent"
                                id="parent"
                                class="form-control form-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach (App\Models\Category::tree()->breadthFirst()->get() as $category)
                                    <option value="{{ $category->id }}">{{ ucfirst($category->name) }}</option>
                                @endforeach
                            </select>
                            <small id="categories-error" class="text-danger d-none"></small>
                        </div>
                    @endif

                    <div class="mb-3">
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
                <button type="reset" id="reset" class="btn btn-danger" form="admin-categories-form">{{ __('Reset') }}</button>
                <button type="submit" id="submit" class="btn btn-primary" form="admin-categories-form">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
</div>
