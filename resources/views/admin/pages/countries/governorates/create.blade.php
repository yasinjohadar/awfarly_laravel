@extends('admin.layouts.app')

@section('title', __('pages/countries/governorates/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/countries/governorates/create.breadcrumb.countries')}}</div>
    <div class="breadcrumb-item active">{{__('pages/countries/governorates/create.breadcrumb.governorates')}}</div>
    <div class="breadcrumb-item active">{{__('pages/countries/governorates/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/countries/governorates/create.content.title')}}</h5>
        </div>
        @livewire('countries.governorates.governorates-create-component')
    </div>
@endsection
