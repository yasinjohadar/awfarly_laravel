@extends('admin.layouts.app')

@section('title', __('pages/countries/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/countries/index.breadcrumb.countries')}}</span>
    <span class="breadcrumb-item active">{{__('pages/countries/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('countries.countries-component')
@endsection
