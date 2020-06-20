@extends('admin.index')

@section('admin-main-content')
    <div id="users-admin" class="admin-section">
        <div class="top-section">
            <h3 class="all-caps">{{ __('Users') }}</h3>

            <div class="d-flex flex-nowrap">
                <div class="filter-box flex-grow-1">
                    <input type="text"
                        class="filter-box-input form-control"
                        data-target=".filter-table"
                        placeholder="{{ __('Filter by') }}...">
                </div>

                <div class="buttons">
                    <button id="btn-create-user"
                        class="btn btn-primary btn-create"
                        type="button"
                        title="{{ __('Create new user') }}"
                        data-toggle="modal"
                        data-target="#user-form-wrapper"
                        aria-expanded="false"
                        disabled>
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        @include('admin.forms.user')

        @if ($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Id') }}</th>
                            <th scope="col">{{ __('Role') }}</th>
                            <th scope="col">{{ __('Username') }}</th>
                            <th scope="col">{{ __('Name') }}</th>
                            <th scope="col">{{ __('Writings') }}</th>
                            <th scope="col">{{ __('Registered') }}</th>
                            <th scope="col">{{ __('Verified') }}</th>
                            <th scope="col">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="filter-table">
                        @foreach ($users as $user)
                            <tr>
                                <th scope="row">{{ $user->id }}</th>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->getName() }}</td>
                                <td>{{ $user->writings()->count() }}</td>
                                <td>{{ $user->created_at }}</td>
                                <td>{{ $user->email_verified_at }}</td>
                                <td class="action-links">
                                    <a href="{{ route('users.show', $user) }}">
                                        <i class="fas fa-fw fa-eye"></i>
                                    </a>

                                    <a href="#" class="admin-edit">
                                        <i class="fas fa-fw fa-edit"></i>
                                    </a>

                                    <a href="#" class="admin-delete">
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

    {{ $users->withQueryString()->links() }}
@endsection
