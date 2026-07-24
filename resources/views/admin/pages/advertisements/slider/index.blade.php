@extends('admin.layouts.app')

@section('title', __('pages/advertisements/slider/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisements/slider/index.breadcrumb.advertisements')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisements/slider/index.breadcrumb.slider')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisements/slider/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('advertisements.slider.slider-advertisements-component')
@endsection
