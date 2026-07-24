@extends('admin.layouts.app')

@section('title', __('pages/advertisements/slider/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/advertisements/slider/create.breadcrumb.advertisements')}}</div>
    <div class="breadcrumb-item active">{{__('pages/advertisements/slider/create.breadcrumb.slider')}}</div>
    <div class="breadcrumb-item active">{{__('pages/advertisements/slider/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/advertisements/slider/create.content.title')}}</h5>
        </div>
        @livewire('advertisements.slider.slider-advertisements-create-component')
    </div>
@endsection
