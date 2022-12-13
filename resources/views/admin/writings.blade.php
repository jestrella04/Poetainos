@extends('admin.index')

@section('admin-main-content')
    <div id="writings-admin" class="admin-section">
        <div class="top-section">
            <h3 class="all-caps">{{ __('Writings') }}</h3>

            <div class="d-flex flex-nowrap">
                <div class="filter-box flex-grow-1">
                    <input type="text"
                        class="filter-box-input form-control"
                        data-wh-target=".filter-table"
                        placeholder="{{ __('Filter by') }}..."
                        autocomplete="off">
                </div>

                <div class="buttons">
                    <a href="{{ route('writings.create') }}"
                        id="btn-create-writing"
                        class="btn btn-primary btn-create"
                        title="{{ __('Create new writing') }}">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>

        @include('partials.delete-modal')

        @if ($writings->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Id') }}</th>
                            <th scope="col">{{ __('Title') }}</th>
                            <th scope="col">{{ __('Categories') }}</th>
                            <th scope="col">{{ __('Author') }}</th>
                            <th scope="col">{{ __('Aura') }}</th>
                            <th scope="col">{{ __('Created') }}</th>
                            <th scope="col">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="filter-table">
                        @foreach ($writings as $writing)
                            <tr>
                                <th scope="row">{{ $writing->id }}</th>
                                <td>{{ $writing->title }}</td>
                                <td>{{ $writing->categoriesAsString() ?? '' }}</td>
                                <td>{{ $writing->author->getName() }}</td>
                                <td>{{ $writing->aura }}</td>
                                <td>{{ $writing->created_at }}</td>
                                <td class="action-links">
                                    <a href="{{ route('writings.show', $writing) }}"
                                        class="btn">
                                        <i class="fas fa-fw fa-eye" aria-hidden="true"></i>
                                    </a>

                                    <a href="{{ route('writings.edit', $writing) }}" class="btn">
                                        <i class="fas fa-fw fa-edit" aria-hidden="true"></i>
                                    </a>

                                    <a href="#delete-modal"
                                        class="admin-content-delete btn"
                                        data-wh-target="{{ route('admin.writings.destroy', $writing) }}"
                                        data-wh-warning="{{ __('Deleting a writing will also delete all likes, shelves, comments and replies tied to that writing') }}.">
                                        <i class="fas fa-fw fa-trash" aria-hidden="true"></i>
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

    {{ $writings->withQueryString()->links() }}
@endsection
