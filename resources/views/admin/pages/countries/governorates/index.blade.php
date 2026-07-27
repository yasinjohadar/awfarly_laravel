@extends('admin.layouts.app')

@section('title', __('pages/countries/governorates/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/countries/governorates/index.breadcrumb.countries')}}</span>
    <span class="breadcrumb-item active">{{__('pages/countries/governorates/index.breadcrumb.governorates')}}</span>
    <span class="breadcrumb-item active">{{__('pages/countries/governorates/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('countries.governorates.governorates-component')
@endsection
