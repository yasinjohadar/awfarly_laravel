@extends('admin.layouts.app')

@section('title', __('pages/customers/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/customers/index.breadcrumb.customers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/customers/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/customers/index.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('customers.customers-inquiry-component', ['activeNumberFilters' => $activeNumberFilters ?? []])
        </div>
    </div>
@endsection
