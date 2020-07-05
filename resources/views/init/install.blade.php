@extends('layouts.master')

@section('title', 'Database Initialization')

@section('body')
    <main role="main" class="container installation-wrapper">
        <div style="width: 90%;
                    max-width: 35rem;
                    margin: 2rem auto;
                    padding: 1rem 2rem;
                    border-radius: 0.6rem;
                    border: 1px solid #e3e3e3;
                    background-color: #ededed">
            <div class="logo text-center mb-4">
                <img src="{{ mix('/static/images/logo.svg') }}" width="96" height="96">
                <h1>{{ config('app.name') }}</h1>
                <p class="lead">Database Initialization</p>

                <small>
                    You are only asked for the most basic and required information here.
                    After the process is completed you will have the choice to be taken
                    to the admin panel, where you can further customize settings to your needs.
                </small>
            </div>

            <form method="POST">
                @csrf

                <div class="form-group row">
                    <label for="site-name" class="col-sm-4 col-form-label">Site name:</label>

                    <div class="col-sm-8">
                        <input
                            type="text"
                            class="form-control @error('site_name') is-invalid @enderror"
                            name="site_name"
                            id="site-name"
                            value="{{ config('app.name') }}"
                            readonly>

                        @error('site_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="site-slogan" class="col-sm-4 col-form-label">Site slogan:</label>

                    <div class="col-sm-8">
                        <input
                            type="text"
                            class="form-control @error('site_slogan') is-invalid @enderror"
                            name="site_slogan"
                            id="site-slogan"
                            value="{{ old('site_slogan') }}"
                            required
                            autocomplete="off">

                        @error('site_slogan')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="admin-username" class="col-sm-4 col-form-label">Admin username:</label>

                    <div class="col-sm-8">
                        <input
                            type="text"
                            class="form-control @error('admin_username') is-invalid @enderror"
                            name="admin_username"
                            id="admin-username"
                            pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$"
                            value="{{ old('admin_username') }}"
                            required
                            autocomplete="off">

                        @error('admin_username')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="admin-email" class="col-sm-4 col-form-label">Admin email:</label>

                    <div class="col-sm-8">
                        <input
                            type="email"
                            class="form-control @error('admin_email') is-invalid @enderror"
                            name="admin_email"
                            id="admin-email"
                            value="{{ old('admin_email') }}"
                            required
                            autocomplete="off">

                        @error('admin_email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="admin-password" class="col-sm-4 col-form-label">Admin password:</label>

                    <div class="col-sm-8">
                        <input
                            type="password"
                            class="form-control @error('admin_password') is-invalid @enderror"
                            name="admin_password"
                            id="admin-password"
                            pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
                            required
                            autocomplete="off">

                        @error('admin_password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="admin-confirm" class="col-sm-4 col-form-label">Confirm password:</label>
                    <div class="col-sm-8">
                        <input
                            type="password"
                            class="form-control"
                            name="admin_password_confirmation"
                            id="admin-confirm"
                            pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
                            required
                            autocomplete="off">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">Initialize</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection
