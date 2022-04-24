<div class="modal fade modal-complaint"
    {{-- id="{{ $params['modal_id'] }}" --}}
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    tabindex="-1"
    aria-labelledby=""
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <p class="modal-title">{{ __('Complaint') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body">
                <form
                    id="form-{{ $params['modal_id'] }}"
                    class="form-post-complaint"
                    action="{{ route('complaints.store') }}"
                    method="POST">
                    @csrf

                    <input type="hidden" name="complainable_type" value="{{ $params['complainable_type'] }}">
                    <input type="hidden" name="complainable_id" value="{{ $params['complainable_id'] }}">

                    <p class="form-text">
                        {{ __('Why do you want to report this content?') }}
                    </p>

                    @foreach ($params['reasons'] as $reason)
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="{{ Str::kebab($reason) }}"
                                name="reasons[]"
                                value="{{ $reason }}">
                            <label class="form-check-label" for="{{ Str::kebab($reason) }}">{{ $reason }}</label>
                        </div>
                    @endforeach

                    <label class="form-label mt-3" style="width: 100%">
                        <p class="form-text">{{ __('Tell us a little bit more...') }}</p>
                        <textarea class="form-control" rows="1" name="comment" maxlength="255"></textarea>
                    </label>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <input
                    type="submit"
                    id="submit"
                    class="btn btn-primary complaint-submit"
                    form="form-{{ $params['modal_id'] }}"
                    value="{{ __('Post Complaint') }}">
            </div>
        </div>
    </div>
</div>
