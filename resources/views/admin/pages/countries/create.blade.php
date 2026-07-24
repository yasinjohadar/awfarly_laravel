@extends('admin.layouts.app')

@section('title', __('pages/countries/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/countries/create.breadcrumb.countries')}}</div>
    <div class="breadcrumb-item active">{{__('pages/countries/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/countries/create.content.title')}}</h5>
        </div>
        @livewire('countries.countries-create-component')
    </div>
@endsection
