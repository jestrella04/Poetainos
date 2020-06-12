@extends('admin.index')

@section('admin-main-content')
    <div id="summary-admin" class="admin-section">
        <div class="d-flex flex-wrap">
            @foreach ($counters as $counter=>$count)
                <div class="admin-counter">
                    <span>{{ $counter }}</span>
                    <span>{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection
