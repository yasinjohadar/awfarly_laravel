@extends('admin.layouts.app')

@section('title', __('pages/countries/cities/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/countries/cities/index.breadcrumb.countries')}}</span>
    <span class="breadcrumb-item active">{{__('pages/countries/cities/index.breadcrumb.cities')}}</span>
    <span class="breadcrumb-item active">{{__('pages/countries/cities/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="card-title mb-0">{{__('pages/countries/cities/index.content.title_all')}}</h5>
            @can('cities.add')
                <a href="{{route('admin.cities.create')}}" class="btn btn-primary">
                    {{__('pages/countries/cities/index.content.add')}}
                </a>
            @endcan
        </div>
        <div class="card-body">
            @livewire('countries.cities.cities-inquiry-component')
        </div>
    </div>
@endsection
