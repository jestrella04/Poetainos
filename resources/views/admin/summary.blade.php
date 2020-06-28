@extends('admin.index')

@section('admin-main-content')
    <div id="summary-admin" class="admin-section">
        <div class="top-section">
            <h3 class="all-caps">{{ __('Summary') }}</h3>
        </div>

        <div class="d-flex flex-wrap">
            @foreach ($counters as $counter)
                <div class="admin-counter">
                    <h3>{{ $counter['count'] }}</h3>
                    <span>{{ __($counter['title']) }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
