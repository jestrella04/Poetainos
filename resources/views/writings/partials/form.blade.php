<div id="writing-form-wrapper" class="form-wrapper">
    <h2 class="title all-caps">
        @if ($writing->exists)
            {{ $params['title']['update'] }}
        @else
        {{ $params['title']['create'] }}
        @endif
    </h2>

    @if ($writing->exists)
        @php $update = route('writings.update', $writing) @endphp
    @endif

    <form id="writing-form" action="{{ $update ?? route('writings.store') }}" method="post">
        @csrf

        @if ($writing->exists)
            @method('put')
        @endif

        <div class="form-group row">
            <label for="title" class="col-sm-2 col-form-label">{{ __('Title') }}:</label>

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
                    required
                    autocomplete="off">
                <small id="title-error" class="text-danger d-none"></small>
            </div>
        </div>

        @if ($mainCategories->count() > 0)
            <div class="form-group row">
                <label for="main-category" class="col-sm-2 col-form-label">{{ __('Main category') }}:</label>

                <div class="col-sm-10">
                    <select
                        name="main_category"
                        id="main-category"
                        class="form-control custom-select"
                        required>
                        <option value="">{{ __('Click to select') }}</option>
                        @foreach ($mainCategories as $category)
                            <option
                                value="{{ $category->id }}"
                                data-descendants="{{ $category->descendants()->depthFirst()->get(['id', 'name'])->toJson() }}"
                                @if (in_array($category->id, $writing->categories->pluck('id')->toArray())) {{ 'selected' }} @endif>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <small id="main-category-error" class="text-danger d-none"></small>
                </div>
            </div>
        @endif

        <div class="form-group row">
            <label for="categories" class="col-sm-2 col-form-label">{{ __('Alternative categories') }}:</label>

            <div class="col-sm-10">
                <select
                    name="categories[]"
                    id="categories"
                    class="form-control"
                    multiple
                    required>
                    {{-- <option value="" data-placeholder="true">{{ __('Click to select') }} {{ __('(1 or more)') }}</option>
                    @foreach ($altCategories as $category)
                        <option value="{{ $category->id }}"
                        @if (in_array($category->id, $writing->categories->pluck('id')->toArray())) {{ 'selected' }} @endif>
                        {{ $category->name }}
                        </option>
                    @endforeach --}}
                </select>

                <small id="categories-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="text" class="col-sm-2 col-form-label">{{ __('Text') }}:</label>

            <div class="col-sm-10">
                <textarea
                    class="form-control visual-editor"
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
            <label for="tags" class="col-sm-2 col-form-label">{{ __('Tags') }}:</label>

            <div class="col-sm-10">
                <input
                    type="text"
                    name="tags"
                    id="tags"
                    class="form-control"
                    value="{{ old('tags', $writing->tagsAsString()) }}"
                    minlength="3"
                    maxlength="50"
                    placeholder=""
                    autocomplete="off"
                    pattern="[a-zA-Z0-9,\s\u00c0-\u00d6\u00d8-\u00f6\u00f8-\u02af\u1d00-\u1d25\u1d62-\u1d65\u1d6b-\u1d77\u1d79-\u1d9a\u1e00-\u1eff\u2090-\u2094\u2184-\u2184\u2488-\u2490\u271d-\u271d\u2c60-\u2c7c\u2c7e-\u2c7f\ua722-\ua76f\ua771-\ua787\ua78b-\ua78c\ua7fb-\ua7ff\ufb00-\ufb06]+">
                <small id="tags-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="link" class="col-sm-2 col-form-label">{{ __('Link') }}:</label>

            <div class="col-sm-10">
                <input
                    type="url"
                    name="link"
                    id="link"
                    class="form-control"
                    value="{{ old('link', isset($writing->extra_info['link']) ? $writing->extra_info['link'] : '') }}"
                    minlength="3"
                    maxlength="250"
                    placeholder=""
                    autocomplete="off">
                <small id="link-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
            <label for="cover" class="col-sm-2 col-form-label">{{ __('Cover') }}:</label>

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
                        <p>
                            <strong>{{ __('Click here to select a file') }}</strong>
                        </p>

                        <p>{{ __('Max file size is :size', ['size' => getSiteConfig('uploads_max_file_size') . 'kb']) }}</p>
                    </div>

                    <span id="selected-file" class="file-info d-none"></span>
                    <span id="selected-error" class="file-info d-none text-danger">{{ __('An error ocurred, please select a different file.') }}</span>
                </button>

                <small id="cover-error" class="text-danger d-none"></small>
            </div>
        </div>

        @if ($writing->exists && auth()->user()->can('delete', $writing))
        <div class="form-group row">
            <div class="offset-sm-2 col-sm-10">
                <a href="#delete-modal"
                    class="user-content-delete btn btn-link btn-sm btn-block text-danger"
                    data-target="{{ route('writings.destroy', $writing) }}"
                    data-redirect="{{ route('home') }}"
                    data-warning="{{ __('Deleting a writing will also delete all votes, shelves, comments and replies tied to that writing') }}.">
                    {{ __('Delete this writing?') }}
                </a>
            </div>
        </div>
        @endif

        <div class="form-group row">
            <div class="offset-sm-2 col-sm-10">
                <button
                    type="submit"
                    id="submit"
                    class="btn btn-primary btn-lg btn-block">
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

@if ($writing->exists && auth()->user()->can('delete', $writing))
    @include('partials.delete-modal')
@endif
