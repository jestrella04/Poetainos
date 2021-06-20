<div id="contact-form-wrapper" class="form-wrapper">
    <h2 class="title all-caps">{{ __('Contact form') }}</h2>

    <form id="contact-form" action="{{ route('contact.create') }}" method="POST">
        @csrf

        <div class="mb-3">
            <input
                type="text"
                name="name"
                id="name"
                class="form-control"
                value="{{ old('name') }}"
                placeholder="{{ __('Enter your name') }}"
                autocomplete="off"
                minlength="3"
                maxlength="40"
                required>
            @error('name')
                <small class="text-danger" role="alert">
                    {{ $message }}
                </small>
            @enderror
        </div>

        <div class="mb-3">
            <input
                type="email"
                name="email"
                id="email"
                class="form-control"
                value="{{ old('email') }}"
                placeholder="{{ __('Enter your email') }}"
                autocomplete="off"
                maxlength="45"
                required>
            @error('email')
                <small class="text-danger" role="alert">
                    {{ $message }}
                </small>
            @enderror
        </div>

        <div class="mb-3">
            <input
                type="text"
                name="subject"
                id="subject"
                class="form-control"
                value="{{ old('subject') }}"
                placeholder="{{ __('Enter the subject') }}"
                autocomplete="off"
                minlength="3"
                maxlength="40"
                required>
            @error('subject')
                <small class="text-danger" role="alert">
                    {{ $message }}
                </small>
            @enderror
        </div>

        <div class="mb-3">
            <textarea
                class="form-control"
                name="message"
                id="message"
                placeholder="{{ __('Enter your message') }}"
                autocomplete="off"
                minlength="100"
                rows="10"
                required>{{ old('message') }}</textarea>
            @error('message')
                <small class="text-danger" role="alert">
                    {{ $message }}
                </small>
            @enderror
        </div>

        <div class="mb-3">
            @captcha

            <input
                type="text"
                name="captcha"
                id="captcha"
                class="form-control mt-3"
                value=""
                placeholder="{{ __('Validate you are not a robot') }}"
                autocomplete="off"
                required>

            @error('captcha')
                <small class="text-danger" role="alert">
                    {{ $message }}
                </small>
            @enderror
        </div>

        <div class="d-grid gap-2 mb-3">
            <button type="submit" id="submit" class="btn btn-primary btn-lg d-title">
                {{ __('Send message') }}
            </button>
        </div>
    </form>
</div>
