<div id="writing-form-wrapper" class="form-wrapper">
    @if ($writing->exists)
        <h3 class="all-caps">{{ $params['title']['update'] }}</h3>
    @else
        <h3 class="all-caps">{{ $params['title']['create'] }}</h3>
    @endif

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

        <div class="form-group row">
            <label for="title" class="col-sm-2 col-form-label">{{ __('Title') }}</label>

            <div class="col-sm-10">
                <input
                    type="text"
                    name="title"
                    id="title"
                    class="form-control"
                    value="{{ old('title', $writing->title) }}"
                    minlength="3"
                    maxlength="100"
                    placeholder=""
                    required>
                <small id="title-error" class="text-danger d-none"></small>
            </div>
        </div>

        @if ($types->count() > 0)
            <div class="form-group row">
                <label for="type" class="col-sm-2 col-form-label">{{ __('Type') }}</label>

                <div class="col-sm-10">
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
            <div class="form-group row">
                <label for="category" class="col-sm-2 col-form-label">{{ __('Category') }}</label>

                <div class="col-sm-10">
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

        <div class="form-group row">
            <label for="text" class="col-sm-2 col-form-label">{{ __('Text') }}</label>

            <div class="col-sm-10">
                <textarea
                    class="form-control"
                    name="text"
                    id="text"
                    rows="15"
                    minlength="10"
                    maxlength="2000"
                    required>{{ old('text', $writing->text) }}</textarea>
                <small id="text-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="tags" class="col-sm-2 col-form-label">{{ __('Tags') }}</label>

            <div class="col-sm-10">
                <input
                    type="text"
                    name="tags"
                    id="tags"
                    class="form-control"
                    value="{{ old('tags', $writing->tagsAsString()) }}"
                    minlength="3"
                    maxlength="50"
                    placeholder="">
                <small id="tags-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="link" class="col-sm-2 col-form-label">{{ __('Link') }}</label>

            <div class="col-sm-10">
                <input
                    type="url"
                    name="link"
                    id="link"
                    class="form-control"
                    value="{{ old('link', $writing->extra_info['link']) }}"
                    minlength="3"
                    maxlength="250"
                    placeholder="">
                <small id="link-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="cover" class="col-sm-2 col-form-label">{{ __('Cover') }}</label>

            <div class="col-sm-10">
                <input
                type="file"
                name="cover"
                id="cover"
                class="form-control-file d-none"
                accept="image/png, image/jpeg"
                data-target="#selected-file"
                data-max-size="{{ getSiteConfig('uploads_max_file_size') }}"
                placeholder="">

                <button id="cover-chooser" data-target="#cover">
                    <div class="placeholder">
                        <p>{{ __('Click here to select a file') }}</p>
                        <p class="text-muted">{{ __('Max file size is :size', ['size' => getSiteConfig('uploads_max_file_size') . 'kb']) }}</p>
                    </div>

                    <span id="selected-file" class="file-info d-none"></span>
                    <span id="selected-error" class="file-info d-none text-danger">{{ __('An error ocurred, please select a different file.') }}</span>
                </button>

                <small id="cover-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
            <div class="offset-sm-2 col-sm-10">
                <button type="submit" id="submit" class="btn btn-dark btn-lg btn-block">
                    @if ($writing->exists)
                        {{ __('Save changes') }}
                    @else
                        {{ __('Publish Writing') }}
                    @endif
                </button>
            </div>
        </div>
    </form>
</div>
