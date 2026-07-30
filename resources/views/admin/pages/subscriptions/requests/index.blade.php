@extends('admin.layouts.app')

@section('title', __('pages/subscriptions/requests/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/subscriptions/requests/index.breadcrumb.subscriptions')}}</span>
    <span class="breadcrumb-item active">{{__('pages/subscriptions/requests/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('subscriptions.requests.subscription-requests-component')
@endsection
