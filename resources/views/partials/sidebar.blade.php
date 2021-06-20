<aside class="col-lg-4 main-sidebar container-fluid">
    <div class="sidebar-toggle d-block d-lg-none">
        <div class="d-grid">
            <a class="btn btn-light" data-bs-toggle="collapse" href="#sidebar-main" role="button" aria-label="{{ __('Toggle sidebar') }}">
                <i class="fa fa-chevron-down"></i>
            </a>
        </div>
    </div>

    <div id="sidebar-main" class="collapse no-collapse-lg">
        {{-- @include('blocks.search') --}}
        @include('blocks.main-categories')
        @include('blocks.alt-categories')
        @include('blocks.pop-tags')
        @include('blocks.featured-authors')
    </div>
</aside>
