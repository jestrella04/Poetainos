@extends('admin.index')

@section('admin-main-content')
    <div id="types-admin" class="admin-section">
        <div class="top-section">
            <h3 class="all-caps">{{ __('Types') }}</h3>

            <div class="d-flex flex-nowrap">
                <div class="filter-box flex-grow-1">
                    <input type="text"
                        class="filter-box-input form-control"
                        data-target=".filter-table"
                        placeholder="{{ __('Filter by') }}...">
                </div>

                <div class="buttons">
                    <button id="btn-create-type"
                        class="btn btn-primary btn-create"
                        type="button"
                        title="{{ __('Create new type') }}"
                        data-toggle="modal"
                        data-target="#admin-types-form-wrapper"
                        aria-expanded="false">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        @include('admin.forms.type')
        @include('partials.delete-modal')

        @if ($types->count() > 0)
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
                        @foreach ($types as $type)
                            <tr>
                                <th scope="row">{{ $type->id }}</th>
                                <td>{{ $type->name }}</td>
                                <td>{{ $type->writings()->count() }}</td>
                                <td>{{ $type->created_at }}</td>
                                <td class="action-links">
                                    <a href="{{ route('types.show', $type) }}"
                                        class="btn">
                                        <i class="fas fa-fw fa-eye"></i>
                                    </a>

                                    <a href="#"
                                        class="admin-edit btn"
                                        data-target-modal="#admin-types-form-wrapper"
                                        data-target-model="type"
                                        data-target-form="#admin-types-form"
                                        data-target-form-data="{{ $type->toJson() }}">
                                        <i class="fas fa-fw fa-edit"></i>
                                    </a>

                                    <a href="#delete-modal"
                                        class="admin-content-delete btn"
                                        data-target="{{ route('admin.types.destroy', $type) }}"
                                        data-warning="{{ __('Deleting a type will not delete associated writings') }}.">
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

    {{ $types->withQueryString()->links() }}
@endsection
