@extends('admin.layouts.app')

@section('title', __('pages/countries/cities/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/countries/cities/create.breadcrumb.countries')}}</div>
    <div class="breadcrumb-item active">{{__('pages/countries/cities/create.breadcrumb.cities')}}</div>
    <div class="breadcrumb-item active">{{__('pages/countries/cities/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/countries/cities/create.content.title')}}</h5>
        </div>
        @livewire('countries.cities.cities-create-component')
    </div>
@endsection
