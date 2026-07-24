@extends('admin.layouts.app')

@section('title', __('pages/subscriptions/payments/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/subscriptions/payments/index.breadcrumb.subscriptions')}}</span>
    <span class="breadcrumb-item active">{{__('pages/subscriptions/payments/index.breadcrumb.payments')}}</span>
    <span class="breadcrumb-item active">{{__('pages/subscriptions/payments/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('subscriptions.payments.payments-component')
@endsection
