@extends('layouts.index')

@section('meta.title', $params['title'])

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div id="contact-form-main-content" class="main-content">
        @include('contact.form')
    </div>
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('footer')
    @include('partials.footer')
@endsection
