@extends('admin.index')

@section('admin-main-content')
    <div id="pages-admin" class="admin-section">
        <h3 class="all-caps">{{ __('Settings') }}</h3>

        @include('admin.forms.settings')
    </div>
@endsection
