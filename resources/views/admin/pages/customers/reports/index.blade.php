@extends('admin.layouts.app')

@section('title', __('pages/customers/reports/reports.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/customers/reports/reports.breadcrumb.customers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/customers/reports/reports.breadcrumb.reports')}}</span>
    <span class="breadcrumb-item active">{{__('pages/customers/reports/reports.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('customers.reports.reported-customers-component')
@endsection
