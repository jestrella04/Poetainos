@extends('admin.index')

@section('admin-main-content')
    <div id="summary-admin" class="admin-section">
        <div class="top-section">
            <h3 class="all-caps">{{ __('Tools') }}</h3>
        </div>

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a
                    class="nav-link active"
                    href="#phpinfo"
                    id="phpinfo-tab"
                    data-bs-toggle="tab"
                    role="tab"
                    aria-controls="phpinfo"
                    aria-selected="true">
                        {{ __('PHP Info') }}
                </a>
            </li>

            <li class="nav-item">
                <a
                    class="nav-link"
                    href="#logview"
                    id="log-tab"
                    data-bs-toggle="tab"
                    role="tab"
                    aria-controls="logview"
                    aria-selected="false">
                        {{ __('Log Viewer') }}
                </a>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="phpinfo" role="tabpanel" aria-labelledby="phpinfo-tab">
                <iframe srcdoc="{{ $params['php_info'] }}"></iframe>
            </div>

            <div class="tab-pane fade" id="logview" role="tabpanel" aria-labelledby="log-tab">
                <textarea class="form-control" rows="20" disabled>{{ $params['log_contents'] }}</textarea>
            </div>
        </div>
    </div>
@endsection
