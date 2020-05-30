@extends('layouts.index')

@if ($writing->exists)
    @section('title', $params['title']['update'])
@else
    @section('title', $params['title']['create'])
@endif

@section('header')
    @include('partials.header')
@endsection

@section('main')
    <div class="main-content">
        @include('writings.partials.form')
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection
