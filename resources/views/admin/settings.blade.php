@extends('admin.index')

@section('admin-main-content')
    <div id="pages-admin" class="admin-section">
        <div id="admin-settings-form-wrapper" class="form-wrapper">
            <h3 class="title all-caps">
                {{ __('Settings') }}
            </h3>

            @include('admin.forms.settings')
        </div>
    </div>
@endsection
