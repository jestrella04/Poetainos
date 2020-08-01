@if ($paginator->hasPages())
    <section class="main-pagination">
        <div class="d-flex justify-content-center">
            <div class="btn-group" role="group">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <a class="btn btn-primary disabled"
                        title="{{ __('Previous') }}"
                        data-toggle="tooltip"
                        data-placement="top"
                        aria-label="{{ __('Previous') }}">
                        <i class="fa fa-chevron-left fa-fw"></i>
                    </a>
                @else
                    <a class="btn btn-primary"
                        href="{{ $paginator->previousPageUrl() }}"
                        title="{{ __('Previous') }}"
                        data-toggle="tooltip"
                        data-placement="top"
                        aria-label="{{ __('Previous') }}">
                        <i class="fa fa-chevron-left fa-fw"></i>
                    </a>
                @endif

                <span class="btn btn-primary disabled">
                    <small>{{ __('Page :page', ['page' => $paginator->currentPage()]) }}</small>
                </span>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a class="btn btn-primary"
                        href="{{ $paginator->nextPageUrl() }}"
                        title="{{ __('Next') }}"
                        data-toggle="tooltip"
                        data-placement="top"
                        aria-label="{{ __('Next') }}">
                        <i class="fa fa-chevron-right fa-fw"></i>
                    </a>
                @else
                    <a class="btn btn-primary disabled"
                        href="{{ $paginator->nextPageUrl() }}"
                        title="{{ __('Next') }}"
                        data-toggle="tooltip"
                        data-placement="top"
                        aria-label="{{ __('Next') }}">
                        <i class="fa fa-chevron-right fa-fw"></i>
                    </a>
                @endif
            </div>
        </div>
    </section>
@endif
