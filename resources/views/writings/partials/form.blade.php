
{{-- <form id="writing-create-form" action="{{ route('writings.store') }}" method="post"> --}}
    @if ($writing->exists)
        @php $update = route('writings.update', $writing) @endphp
    @endif

    <form id="writing-form" action="{{ $update ?? route('writings.store') }}" method="post">
    @csrf

    @if ($writing->exists)
        @method('put')
    @endif

    <div class="alert alert-success d-none" role="alert">
        <button type="button" class="close">
            <span class="alert-link">&times;</span>
        </button>

        <h6 class="alert-heading">
            @if ($writing->exists)
                {{ __('Your writing was successfully updated') }}
            @else
                {{ __('Your writing was successfully posted') }}
            @endif
        </h6>

        <p>
            <a href="#" id="writing-success-link" class="alert-link">{{ __('Take a look for yourself') }}</a>
        </p>
    </div>

    <div class="alert alert-danger d-none" role="alert">
        <button type="button" class="close">
            <span class="danger-link">&times;</span>
        </button>

        <h6 class="alert-heading">{{ __('It looks like something went wrong') }}</h6>
        <p>{{ __('Please review the information below') }}</p>
    </div>

    <div class="form-group">
        <label for="title">{{ __('Title') }}</label>

        <div>
            <input
                type="text"
                name="title"
                id="title"
                class="form-control"
                value="{{ old('title', $writing->title) }}"
                placeholder=""
                required>
            <small id="title-error" class="text-danger d-none"></small>
        </div>
    </div>

    @if ($types->count() > 0)
        <div class="form-group">
            <label for="type">{{ __('Type') }}</label>

            <div>
                <select
                    name="type"
                    id="type"
                    class="form-control custom-select"
                    required>
                    <option value="">{{ __('Click to select') }}</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @if ($type->id === $writing->type_id) {{ 'selected' }} @endif>{{ $type->name }}</option>
                    @endforeach
                </select>

                <small id="type-error" class="text-danger d-none"></small>
            </div>
        </div>
    @endif

    @if ($categories->count() > 0)
        <div class="form-group">
            <label for="category">{{ __('Category') }}</label>

            <div>
                <select
                    name="category"
                    id="category"
                    class="form-control custom-select"
                    required>
                    <option value="">{{ __('Click to select') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @if ($category->id === $writing->category_id) {{ 'selected' }} @endif>{{ $category->name }}</option>
                    @endforeach
                </select>

                <small id="category-error" class="text-danger d-none"></small>
            </div>
        </div>
    @endif

    <div class="form-group">
        <label for="text">{{ __('Text') }}</label>

        <div>
            <textarea
                class="form-control"
                name="text"
                id="text"
                rows="15"
                required>{{ old('text', $writing->text) }}</textarea>
            <small id="text-error" class="text-danger d-none"></small>
        </div>
    </div>

    <div class="form-group">
        <label for="tags">{{ __('Tags') }}</label>

        <div>
            <input
                type="text"
                name="tags"
                id="tags"
                class="form-control"
                value="{{ old('tags', $writing->tagsAsString()) }}"
                placeholder="">
            <small id="tags-error" class="text-danger d-none"></small>
        </div>
    </div>

    <div class="form-group">
        <label for="link">{{ __('Link') }}</label>

        <div>
            <input
                type="url"
                name="link"
                id="link"
                class="form-control"
                value="{{ old('link', $writing->extra_info['link']) }}"
                placeholder="">
            <small id="link-error" class="text-danger d-none"></small>
        </div>
    </div>

    <div class="form-group">
        <label for="file">{{ __('Cover') }}</label>

        <div>
            <input
            type="file"
            name="cover"
            id="cover"
            class="form-control-file d-none"
            accept="image/png, image/jpeg"
            data-target="#selected-file"
            placeholder="">

            <button id="cover-chooser" data-target="#cover">
                <div class="placeholder">
                    <p>{{ __('Click here to select a file') }}</p>
                    {{-- <p>{{ __('You can also drop your desired image file here') }}</p> --}}
                </div>

                <span id="selected-file"></span>
            </button>

            <small id="cover-error" class="text-danger d-none"></small>
        </div>
    </div>

    <div class="form-group">
        <label></label>

        <div>
            <button type="submit" class="btn btn-dark btn-lg btn-block">
                @if ($writing->exists)
                    {{ __('Save changes') }}
                @else
                    {{ __('Publish Writing') }}
                @endif
            </button>
        </div>
    </div>
</form>
