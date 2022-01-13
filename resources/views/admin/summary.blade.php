@extends('admin.index')

@section('admin-main-content')
    <div id="summary-admin" class="admin-section">
        <div class="top-section">
            <h3 class="all-caps">{{ __('Summary') }}</h3>
        </div>

        <div class="d-flex flex-wrap justify-content-center">
            @foreach ($counters as $counter)
                <div class="flex-fill admin-counter">
                    <div>
                        <p class="h4">{{ getReadableNumber($counter['count']) }}</p>
                        <span class="lead smaller">{{ __($counter['title']) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
