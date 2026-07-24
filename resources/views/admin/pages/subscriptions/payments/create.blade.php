@extends('admin.layouts.app')

@section('title', __('pages/subscriptions/packages/create.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/subscriptions/packages/create.breadcrumb.subscriptions')}}</span>
    <span class="breadcrumb-item active">{{__('pages/subscriptions/packages/create.breadcrumb.packages')}}</span>
    <span class="breadcrumb-item active">{{__('pages/subscriptions/packages/create.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/subscriptions/packages/create.content.title')}}</h5>
        </div>
        @livewire('subscriptions.packages.packages-create-component')
    </div>
@endsection
