        <p class="form-text mb-2">{{ __('You need to agree to our terms of service before continuing. Don\'t worry, you\'ll only do it once.') }}</p>
        <div class="form-check form-switch mb-2">
            <input id="service-agreement"
                type="checkbox"
                class="form-check-input"
                name="service_agreement"
                required
                role="switch">

            <label class="form-check-label" for="service-agreement">{{ __('I accept the terms of use') }}.</label>

            <a href="pages/condiciones-de-uso">
                <i class="fa fa-arrow-up-right-from-square" aria-hidden="true"></i>
            </a>
        </div>

        <div class="form-check form-switch">
            <input id="privacy-agreement"
                type="checkbox"
                class="form-check-input"
                name="privacy_agreement"
                required
                role="switch">

            <label class="form-check-label" for="privacy-agreement">{{ __('I accept the privacy policy') }}.</label>

            <a href="pages/politicas-de-privacidad">
                <i class="fa fa-arrow-up-right-from-square" aria-hidden="true"></i>
            </a>
        </div>
