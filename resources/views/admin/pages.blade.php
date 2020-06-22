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
                        data-target="#admin-pages-form-wrapper"
                        aria-expanded="false">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        @include('admin.forms.page')
        @include('partials.delete-modal')

        @if ($pages->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Id') }}</th>
                            <th scope="col">{{ __('Title') }}</th>
                            <th scope="col">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="filter-table">
                        @foreach ($pages as $page)
                            <tr>
                                <th scope="row">{{ $page->id }}</th>
                                <td>{{ $page->title }}</td>
                                <td class="action-links">
                                    <a href="{{ route('pages.show', $page) }}"
                                        class="btn">
                                        <i class="fas fa-fw fa-eye"></i>
                                    </a>

                                    <a href="#"
                                        class="admin-edit btn"
                                        data-target-modal="#admin-pages-form-wrapper"
                                        data-target-model="page"
                                        data-target-form="#admin-pages-form"
                                        data-target-form-data="{{ $page->toJson() }}">
                                        <i class="fas fa-fw fa-edit"></i>
                                    </a>

                                    <a href="#delete-modal"
                                        class="admin-content-delete btn"
                                        data-target="{{ route('admin.pages.destroy', $page) }}">
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
