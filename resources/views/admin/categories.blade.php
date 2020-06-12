@extends('admin.index')

@section('admin-main-content')
    <div id="categories-admin" class="admin-section">
        <div class="top-section">
            <h3 class="all-caps">{{ __('Categories') }}</h3>

            <div class="d-flex flex-nowrap">
                <div class="filter-box flex-grow-1">
                    <input type="text"
                        class="filter-box-input form-control"
                        data-target=".filter-table"
                        placeholder="{{ __('Filter by') }}...">
                </div>

                <div class="buttons">
                    <button id="btn-create-category"
                        class="btn btn-primary btn-create"
                        type="button"
                        title="{{ __('Create new category') }}"
                        data-toggle="modal"
                        data-target="#admin-categories-form-wrapper"
                        aria-expanded="false">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        @include('admin.forms.category')

        @if ($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Id') }}</th>
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('Writings') }}</th>
                            <th scope="col">{{ __('Created at') }}</th>
                            <th scope="col">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="filter-table">
                        @foreach ($categories as $category)
                            <tr>
                                <th scope="row">{{ $category->id }}</th>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->writings()->count() }}</td>
                                <td>{{ $category->created_at }}</td>
                                <td class="action-links">
                                    <a href="{{ route('categories.show', $category) }}">
                                        <i class="fas fa-fw fa-eye"></i>
                                    </a>

                                <a href="{{ route('categories.show', $category) }}">
                                        <i class="fas fa-fw fa-edit"></i>
                                    </a>

                                    <a href="{{ route('categories.show', $category) }}">
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

    {{ $categories->withQueryString()->links() }}
@endsection
