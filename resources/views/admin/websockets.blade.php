@extends('admin.index')

@section('admin-main-content')
    <div id="pages-admin" class="admin-section">
        <div id="admin-settings-form-wrapper" class="form-wrapper">
            <h3 class="title all-caps">
                {{ __('Websockets') }}
            </h3>

            <iframe src="admin/websockets-dashboard" />
        </div>
    </div>
@endsection
