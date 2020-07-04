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
        </div>

        <div style="width: 70%; margin: 0 auto;">
            <div class="alert alert-success text-center mb-4">
                <small>Whooray, the process was completed successfully!</small>
            </div>

            <div class="buttons">
                <a class="btn btn-primary btn-lg btn-block" href="{{ route('home') }}" role="button">
                    <i class="fas fa-home fa-fw"></i>
                    Go to the homepage
                </a>

                <a class="btn btn-primary btn-lg btn-block" href="{{ route('admin.index') }}" role="button">
                    <i class="fas fa-cogs fa-fw"></i>
                    Go to the admin panel
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
