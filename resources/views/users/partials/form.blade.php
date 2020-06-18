@php $avatarSrc = $user->avatarPath() ?? '' @endphp
<div id="profile-form-wrapper" class="form-wrapper">
    <h3 class="title all-caps">{{ __('Update profile') }}</h3>

    <form id="profile-form" action="{{ route('users.edit', $user) }}" method="POST">
        @csrf

        @method('put')

        <div class="alert alert-success d-none" role="alert">
            <button type="button" class="close">
                <span class="alert-link">&times;</span>
            </button>

            <h6 class="alert-heading">
                {{ __('Your profile was successfully updated') }}
            </h6>

            <p>
                <a href="#" id="profile-success-link" class="alert-link">{{ __('Take a look for yourself') }}</a>
            </p>
        </div>

        <div class="alert alert-danger d-none" role="alert">
            <button type="button" class="close">
                <span class="danger-link">&times;</span>
            </button>

            <h6 class="alert-heading">{{ __('It looks like something went wrong') }}</h6>
            <p>{{ __('Please review the information below') }}</p>
        </div>

        <div class="form-group text-center">
            <span class="avatar-chooser" data-target="#avatar">
                <img src="{{ $avatarSrc }}" alt="" class="avatar avatar-preview">

                <i class="fas fa-camera"></i>
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
                data-max-size="{{ getSiteConfig('uploads_max_file_size') }}"
                placeholder="">
        </div>

        <div class="form-group row">
            <div class="offset-3 col-sm-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="avatar-remove" id="avatar-remove" value="1">
                        <span>{{ __('Check if you just want to remove your current avatar') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="name" class="col-sm-3 col-form-label">{{ __('Name') }}:</label>
            <div class="col-sm-9">
                <input type="text"
                    class="form-control"
                    name="name"
                    id="name"
                    value="{{ @old('name', $user->getName()) }}"
                    minlength="3"
                    maxlength="60"
                    placeholder=""
                    required
                    autofocus>
                <small id="name-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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
                    pattern="[A-Za-z0-9]+"
                    disabled>
                <small id="username-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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
                    required>
                <small id="email-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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

        <div class="form-group row">
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
                    placeholder="">
                <small id="location-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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
                    placeholder="">
                <small id="occupation-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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
                    placeholder="">
                <small id="interests-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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
                    placeholder="">
                <small id="website-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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
                    pattern="[A-Za-z0-9]+"
                    placeholder="">
                <small id="twitter-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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
                    pattern="[A-Za-z0-9]+"
                    placeholder="">
                <small id="instagram-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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
                    pattern="[A-Za-z0-9]+"
                    placeholder="">
                <small id="facebook-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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
                    pattern="[A-Za-z0-9]+"
                    placeholder="">
                <small id="youtube-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
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
                    pattern="[A-Za-z0-9]+"
                    placeholder="">
                <small id="goodreads-error" class="text-danger d-none"></small>
            </div>
        </div>

        <div class="form-group row">
            <div class="offset-sm-3 col-sm-9">
                <button
                    type="submit"
                    class="btn btn-dark btn-lg btn-block"
                    id="submit">
                    {{ __('Save changes') }}
                </button>
            </div>
        </div>
    </form>
</div>
