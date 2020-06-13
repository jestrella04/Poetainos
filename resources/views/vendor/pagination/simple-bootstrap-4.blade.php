@if ($paginator->hasPages())
    <section class="main-pagination">
        <div class="d-flex justify-content-center">
            <div class="btn-group" role="group">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <a class="btn btn-dark disabled"
                        title="{{ __('Previous') }}"
                        data-toggle="tooltip"
                        data-placement="top">
                        <i class="fa fa-chevron-left fa-fw"></i>
                    </a>
                @else
                    <a class="btn btn-dark"
                        href="{{ $paginator->previousPageUrl() }}"
                        title="{{ __('Previous') }}"
                        data-toggle="tooltip"
                        data-placement="top">
                        <i class="fa fa-chevron-left fa-fw"></i>
                    </a>
                @endif

                <span class="btn btn-dark disabled">
                    <small>{{ __('Page :page', ['page' => $paginator->currentPage()]) }}</small>
                </span>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a class="btn btn-dark"
                        href="{{ $paginator->nextPageUrl() }}"
                        title="{{ __('Next') }}"
                        data-toggle="tooltip"
                        data-placement="top">
                        <i class="fa fa-chevron-right fa-fw"></i>
                    </a>
                @else
                    <a class="btn btn-dark disabled"
                        href="{{ $paginator->nextPageUrl() }}"
                        title="{{ __('Next') }}"
                        data-toggle="tooltip"
                        data-placement="top">
                        <i class="fa fa-chevron-right fa-fw"></i>
                    </a>
                @endif
            </div>
        </div>
    </section>
@endif
