<form id="admin-settings-form" action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    @method('put')

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

    <div class="form-group">
        <label for="json" class="col-form-label text-primary">
            <small>
                {{ __('You are manually editing the JSON settings, be sure you understand the changes before saving') }}.
            </small>
        </label>

        <textarea
            class="form-control settings-json-input"
            name="json"
            id="json"
            rows="20"
            required>{{ $settings }}</textarea>
        <small id="json-error" class="text-danger d-none"></small>
    </div>

    <div class="float-right">
        <button type="reset" id="reset" class="btn btn-danger">{{ __('Reset') }}</button>
        <button type="submit" id="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
    </div>
</form>
