@extends('layouts.master')

@section('meta.title', 'Database Initialization')

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
            <img src="{{ mix('/static/images/logo.svg') }}" width="96" height="96" alt="">
            <h1>{{ config('app.name') }}</h1>
            <p class="lead">Database Initialization</p>
        </div>

        <div style="width: 70%; margin: 0 auto;">
            <div class="alert alert-success text-center mb-4">
                <small>Whooray, the process was completed successfully!</small>
            </div>

            <div class="d-grid gap-2 mb-3">
                <a class="btn btn-primary btn-lg" href="{{ route('home') }}" role="button">
                    <i class="fas fa-home fa-fw" aria-hidden="true"></i>
                    Go to the homepage
                </a>

                <a class="btn btn-primary btn-lg" href="{{ route('admin.index') }}" role="button">
                    <i class="fas fa-cogs fa-fw" aria-hidden="true"></i>
                    Go to the admin panel
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
