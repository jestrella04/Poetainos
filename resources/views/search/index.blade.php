@extends('layouts.index')

@section('meta.title', $params['title'])

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="search-main-content" class="main-content">
        <div class="input-group">
            <input
                type="text"
                class="form-control"
                placeholder=""
                value="{{ $params['query'] ?? '' }}"
                autocomplete="off">

            <div class="input-group-append">
                <button class="btn btn-primary" type="button" id="">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

