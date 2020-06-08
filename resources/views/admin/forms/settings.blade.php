<form id="settings-form" action="" method="POST">
    @csrf

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
        <button type="reset" class="btn btn-danger">{{ __('Reset') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
    </div>
</form>
