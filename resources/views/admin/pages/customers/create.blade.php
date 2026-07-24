@extends('admin.layouts.app')

@section('title', __('pages/customers/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/customers/create.breadcrumb.customers')}}</div>
    <div class="breadcrumb-item active">{{__('pages/customers/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/customers/create.content.title')}}</h5>
        </div>
        @livewire('customers.customers-create-component')
    </div>
@endsection
