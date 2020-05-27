@extends('layouts.index')

@section('title', $params['title'])

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="search-main-content" class="main-content">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Recipient's username" value="{{ $params['query'] ?? '' }}">

            <div class="input-group-append">
                <button class="btn btn-dark" type="button" id="">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection

