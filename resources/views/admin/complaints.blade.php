@extends('admin.index')

@section('admin-main-content')
    <div id="pages-admin" class="admin-section">
        <div class="top-section">
            <h3 class="all-caps">{{ __('Complaints') }}</h3>

            <div class="d-flex flex-nowrap">
                <div class="filter-box flex-grow-1">
                    <input type="text"
                        class="filter-box-input form-control"
                        data-wh-target=".filter-table"
                        placeholder="{{ __('Filter by') }}..."
                        autocomplete="off">
                </div>
            </div>
        </div>

        @include('admin.forms.page')
        @include('partials.delete-modal')

        @if ($complaints->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Id') }}</th>
                            <th scope="col">{{ __('Type') }}</th>
                            <th scope="col">{{ __('Comment') }}</th>
                            <th scope="col">{{ __('Date') }}</th>
                            <th scope="col">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="filter-table">
                        @foreach ($complaints as $complaint)
                            <tr>
                                <th scope="row">{{ $complaint->id }}</th>
                                <td>{{ $complaint->complainable_type }}</td>
                                <td>{{ $complaint->comment }}</td>
                                <td>{{ $complaint->created_at }}</td>
                                <td class="action-links">
                                    <a href="#" class="btn">
                                        <i class="fas fa-fw fa-eye" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            @include('partials.empty')
        @endif
    </div>

    {{ $complaints->withQueryString()->links() }}
@endsection
