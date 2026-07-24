@extends('admin.layouts.app')

@section('title', __('pages/subscriptions/packages/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/subscriptions/packages/index.breadcrumb.subscriptions')}}</span>
    <span class="breadcrumb-item active">{{__('pages/subscriptions/packages/index.breadcrumb.packages')}}</span>
    <span class="breadcrumb-item active">{{__('pages/subscriptions/packages/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('subscriptions.packages.packages-component', ['filter_id' => $filter_id ?? null])
@endsection
