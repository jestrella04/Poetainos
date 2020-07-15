<aside class="col-lg-4 main-sidebar">
    <div class="sidebar-toggle d-block d-lg-none">
        <a class="btn btn-light btn-block" data-toggle="collapse" href="#sidebar-main" role="button" aria-label="{{ __('Toggle sidebar') }}">
            <i class="fa fa-chevron-down"></i>
        </a>
    </div>

    <div id="sidebar-main" class="collapse no-collapse-lg">
        {{-- @include('blocks.search') --}}
        @include('blocks.main-categories')
        @include('blocks.pop-categories')
        @include('blocks.pop-tags')
        @include('blocks.featured-authors')
    </div>
</aside>
