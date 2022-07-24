@if ($paginator->hasPages())
    <section class="main-pagination">
        <div class="d-flex justify-content-center">
            <div class="btn-group" role="group">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <a class="btn btn-primary pagination-prev disabled"
                        title="{{ __('Previous') }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        aria-label="{{ __('Previous') }}"
                        role="button">
                        <i class="fa fa-chevron-left fa-fw" aria-hidden="true"></i>
                    </a>
                @else
                    <a class="btn btn-primary pagination-prev"
                        href="{{ $paginator->previousPageUrl() }}"
                        title="{{ __('Previous') }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        aria-label="{{ __('Previous') }}"
                        role="button">
                        <i class="fa fa-chevron-left fa-fw" aria-hidden="true"></i>
                    </a>
                @endif

                <span class="btn btn-primary disabled" aria-disabled="true">
                    <small>{{ __('Page :page', ['page' => $paginator->currentPage()]) }}</small>
                </span>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a class="btn btn-primary pagination-next"
                        href="{{ $paginator->nextPageUrl() }}"
                        title="{{ __('Next') }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        aria-label="{{ __('Next') }}"
                        role="button">
                        <i class="fa fa-chevron-right fa-fw" aria-hidden="true"></i>
                    </a>
                @else
                    <a class="btn btn-primary pagination-next disabled"
                        href="{{ $paginator->nextPageUrl() }}"
                        title="{{ __('Next') }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        aria-label="{{ __('Next') }}"
                        role="button">
                        <i class="fa fa-chevron-right fa-fw" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </div>
    </section>
@endif
