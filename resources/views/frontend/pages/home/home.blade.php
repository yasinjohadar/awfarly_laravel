@extends('frontend.layouts.app')

@section('title', __('frontend/home/home.breadcrumb.title'))

@section('content')
    @livewire('frontend.home.home-component')
@endsection
