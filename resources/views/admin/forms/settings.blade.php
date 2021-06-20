<form id="admin-settings-form" action="{{ route('admin.settings.edit') }}" method="POST">
    @csrf
    @method('put')

    <div class="mb-3">
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

    <div class="float-end">
        <button type="reset" id="reset" class="btn btn-danger">{{ __('Reset') }}</button>
        <button type="submit" id="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
    </div>
</form>
