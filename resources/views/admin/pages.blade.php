@extends('admin.index')

@section('admin-main-content')
    <div id="pages-admin" class="admin-section">
        <div class="top-section">
            <h3 class="all-caps">{{ __('Pages') }}</h3>

            <div class="d-flex flex-nowrap">
                <div class="filter-box flex-grow-1">
                    <input type="text"
                        class="filter-box-input form-control"
                        data-target=".filter-table"
                        placeholder="{{ __('Filter by') }}...">
                </div>

                <div class="buttons">
                    <button id="btn-create-page"
                        class="btn btn-primary btn-create"
                        type="button"
                        title="{{ __('Create new page') }}"
                        data-toggle="modal"
                        data-target="#page-form-wrapper"
                        aria-expanded="false">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        @include('admin.forms.page')

        @if ($pages->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Id') }}</th>
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('Slug') }}</th>
                            <th scope="col">{{ __('Writings') }}</th>
                            <th scope="col">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="filter-table">
                        @foreach ($pages as $page)
                            <tr>
                                <th scope="row">{{ $page->id }}</th>
                                <td>{{ $page->name }}</td>
                                <td>{{ $page->slug }}</td>
                                <td>{{ $page->writings()->count() }}</td>
                                <td class="action-links">
                                    <a href="{{ route('pages.show', $page) }}">
                                        <i class="fas fa-fw fa-eye"></i>
                                    </a>

                                <a href="{{ route('pages.show', $page) }}">
                                        <i class="fas fa-fw fa-edit"></i>
                                    </a>

                                    <a href="{{ route('pages.show', $page) }}">
                                        <i class="fas fa-fw fa-trash"></i>
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

    {{ $pages->withQueryString()->links() }}
@endsection
