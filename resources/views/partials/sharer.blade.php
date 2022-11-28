<div class="modal fade modal-sharer" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby=""
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <p class="modal-title">{{ __('Share content') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body">
                <p class="smaller text-muted">{{ $title }}</p>
                <p class="smaller text-muted">{{ cropify($url) }}</p>
                <div class="social-links d-flex flex-wrap">
                    @foreach (shareLinks($title, $url) as $serviceName => $serviceData)
                        <span class="flex-fill">
                            <a class="{{ $serviceData['class'] }}" href="{{ $serviceData['url'] }}" rel="noopener"
                                title="{{ ucfirst($serviceName) }}" data-bs-dismiss="modal">
                                <i class="{{ $serviceData['icon'] }} fa-3x" aria-hidden="true"></i>
                            </a>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
