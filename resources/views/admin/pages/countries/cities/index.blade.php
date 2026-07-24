@extends('admin.layouts.app')

@section('title', __('pages/countries/cities/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/countries/cities/index.breadcrumb.countries')}}</span>
    <span class="breadcrumb-item active">{{__('pages/countries/cities/index.breadcrumb.cities')}}</span>
    <span class="breadcrumb-item active">{{__('pages/countries/cities/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/countries/cities/index.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('countries.cities.cities-inquiry-component')
        </div>
    </div>
@endsection
