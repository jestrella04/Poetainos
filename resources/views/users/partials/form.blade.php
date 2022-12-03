@php $avatarSrc = $user->avatarPath() ?? '' @endphp
<div id="profile-form-wrapper" class="form-wrapper">
    <h2 class="title all-caps">{{ __('Update profile') }}</h2>

    <form id="profile-form" action="{{ route('users.update', $user) }}" method="POST">
        @csrf

        @method('put')

        <div class=" text-center mb-3">
            <span class="avatar-chooser" data-wh-target="#avatar">
                <img src="{{ $avatarSrc }}" alt="" class="avatar avatar-xxl avatar-preview">

                <i class="fas fa-camera" aria-hidden="true"></i>
            </span>

            <div>
                <small id="avatar-error" class="text-danger d-none">{{ __('An error ocurred, please select a different file.') }}</small>
            </div>
        </div>

        <div class="d-none">
            <input
                type="file"
                name="avatar"
                id="avatar"
                class="form-control-file"
                accept="image/png, image/jpeg"
                data-wh-max-size="{{ getSiteConfig('uploads_max_file_size') }}"
                placeholder="">
        </div>

        <div class="row mb-3">
            <div class="offset-sm-3 col-sm-9">
                <div class="form-check form-switch">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="avatar-remove" id="avatar-remove" value="1">
                        <span>{{ __('Remove current avatar') }}</span>
                    </label>
                </div>
            </div>
        </div>

        @if (auth()->user()->isAllowed('admin'))
            <div class="row mb-3">
                <label for="role" class="col-sm-3 col-form-label">{{ __('Role') }}:</label>
                <div class="col-sm-9">
                    <select class="form-control form-select" name="role" id="role">
                        <option value="">{{ __('User') }}</option>
                        @foreach (App\Models\Role::all() as $role)
                            <option value="{{ $role->id }}" @if ($user->role_id === $role->id) {{ 'selected' }} @endif>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    <small id="role-error" class="text-danger d-none"></small>
                </div>
            </div>
        @endif

        <div class="row mb-3">
            <label for="name" class="col-sm-3 col-form-label">{{ __('Name') }}:</label>
            <div class="col-sm-9">
                <input type="text"
                    class="form-control"
                    name="name"
                    id="name"
                    value="{{ @old('name', $user->name) }}"
                    minlength="3"
                    maxlength="60"
                    placeholder=""
                    required
                    autocomplete="off">
                <small id="name-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="username" class="col-sm-3 col-form-label">{{ __('Username') }}:</label>
            <div class="col-sm-9">
                <input
                    type="text"
                    class="form-control"
                    name="username"
                    id="username"
                    value="{{ @old('username', $user->username) }}"
                    minlength="3"
                    maxlength="40"
                    placeholder=""
                    pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$"
                    disabled
                    autocomplete="off">
                <small id="username-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="email" class="col-sm-3 col-form-label">{{ __('Email') }}:</label>
            <div class="col-sm-9">
                <input
                    type="email"
                    class="form-control"
                    name="email"
                    id="email"
                    value="{{ @old('email', $user->email) }}"
                    minlength="3"
                    maxlength="40"
                    placeholder=""
                    required
                    autocomplete="off">
                <small id="email-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="bio" class="col-sm-3 col-form-label">{{ __('Bio') }}:</label>
            <div class="col-sm-9">
                <textarea
                    class="form-control"
                    name="bio"
                    id="bio"
                    rows="3"
                    minlength="3"
                    maxlength="300"
                    required>{{ @old('bio', $user->extra_info['bio']) }}</textarea>
                <small id="bio-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="location" class="col-sm-3 col-form-label">{{ __('Location') }}:</label>
            <div class="col-sm-9">
                <input
                    type="text"
                    class="form-control"
                    name="location"
                    id="location"
                    value="{{ @old('location', $user->extra_info['location']) }}"
                    minlength="3"
                    maxlength="40"
                    placeholder=""
                    autocomplete="off">
                <small id="location-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="occupation" class="col-sm-3 col-form-label">{{ __('Occupation') }}:</label>
            <div class="col-sm-9">
                <input
                    type="text"
                    class="form-control"
                    name="occupation"
                    id="occupation"
                    value="{{ @old('occupation', $user->extra_info['occupation']) }}"
                    minlength="3"
                    maxlength="40"
                    placeholder=""
                    autocomplete="off">
                <small id="occupation-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="interests" class="col-sm-3 col-form-label">{{ __('Interests') }}:</label>
            <div class="col-sm-9">
                <input
                    type="text"
                    class="form-control"
                    name="interests"
                    id="interests"
                    value="{{ @old('interests', $user->extra_info['interests']) }}"
                    minlength="3"
                    maxlength="100"
                    placeholder=""
                    autocomplete="off">
                <small id="interests-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="website" class="col-sm-3 col-form-label">{{ __('Website') }}:</label>
            <div class="col-sm-9">
                <input
                    type="url"
                    class="form-control"
                    name="website"
                    id="website"
                    value="{{ @old('website', $user->extra_info['website']) }}"
                    minlength="3"
                    maxlength="250"
                    placeholder=""
                    autocomplete="off">
                <small id="website-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="twitter" class="col-sm-3 col-form-label">{{ __('Twitter') }}:</label>
            <div class="col-sm-9">
                <input
                    type="text"
                    class="form-control"
                    name="twitter"
                    id="twitter"
                    value="{{ @old('twitter', $user->extra_info['social']['twitter']) }}"
                    minlength="3"
                    maxlength="40"
                    pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$"
                    placeholder=""
                    autocomplete="off">
                <small id="twitter-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="instagram" class="col-sm-3 col-form-label">{{ __('Instagram') }}:</label>
            <div class="col-sm-9">
                <input
                    type="text"
                    class="form-control"
                    name="instagram"
                    id="instagram"
                    value="{{ @old('instagram', $user->extra_info['social']['instagram']) }}"
                    minlength="3"
                    maxlength="40"
                    pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$"
                    placeholder=""
                    autocomplete="off">
                <small id="instagram-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="facebook" class="col-sm-3 col-form-label">{{ __('Facebook') }}:</label>
            <div class="col-sm-9">
                <input
                    type="text"
                    class="form-control"
                    name="facebook"
                    id="facebook"
                    value="{{ @old('facebook', $user->extra_info['social']['facebook']) }}"
                    minlength="3"
                    maxlength="40"
                    pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$"
                    placeholder=""
                    autocomplete="off">
                <small id="facebook-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="youtube" class="col-sm-3 col-form-label">{{ __('Youtube') }}:</label>
            <div class="col-sm-9">
                <input
                    type="text"
                    class="form-control"
                    name="youtube"
                    id="youtube"
                    value="{{ @old('youtube', $user->extra_info['social']['youtube']) }}"
                    minlength="3"
                    maxlength="40"
                    pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$"
                    placeholder=""
                    autocomplete="off">
                <small id="youtube-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="row mb-3">
            <label for="goodreads" class="col-sm-3 col-form-label">{{ __('Goodreads') }}:</label>
            <div class="col-sm-9">
                <input
                    type="text"
                    class="form-control"
                    name="goodreads"
                    id="goodreads"
                    value="{{ @old('goodreads', $user->extra_info['social']['goodreads']) }}"
                    minlength="3"
                    maxlength="40"
                    pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$"
                    placeholder=""
                    autocomplete="off">
                <small id="goodreads-error" class="text-danger d-none"></small>
            </div>
        </div>

        @if (auth()->user()->is($user) && ! auth()->user()->isInAgreement())
        <div class="row mb-3" id="agreements">
            <div class="offset-sm-3 col-sm-9 d-grid gap-2">
                @include('partials.agreement-form')
            </div>
        </div>
        @endif

        <div class="row mb-3">
            <div class="offset-sm-3 col-sm-9 d-grid gap-2">
                <a class="btn btn-link btn-sm mb-1"
                    data-bs-toggle="collapse"
                    href="#user-profile-delete"
                    role="button"
                    aria-expanded="false"
                    aria-controls="user-profile-delete">
                    {{ __('Delete account?') }}
                </a>

                <div class="collapse" id="user-profile-delete">
                    <div class="card card-body d-grid gap-2">
                        <p>
                            {{ __("We're sorry to see you go") }}.
                            {{ __('Please be aware that when you delete your account all related content will also be removed') }},
                            {{ __('including but not limited to: writings, comments and replies, likes, shelved items, etc.') }}
                        </p>

                        <button
                            type="submit"
                            class="btn btn-danger"
                            form="user-delete-form">
                            {{ __('Delete account') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="offset-sm-3 col-sm-9 d-grid gap-2">
                <button
                    type="submit"
                    class="btn btn-primary btn-lg"
                    id="submit">
                    {{ __('Save changes') }}
                </button>
            </div>
        </div>
    </form>

    <form id="user-delete-form" class="d-inline" method="POST" action="{{ route('users.destroy', $user) }}">
        @csrf

        @method('delete')
    </form>
</div>
