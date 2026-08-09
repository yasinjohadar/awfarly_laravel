@extends('admin.layouts.app')

@section('title', __('pages/subscriptions/packages/edit.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/subscriptions/packages/edit.breadcrumb.subscriptions')}}</span>
    <span class="breadcrumb-item active">{{__('pages/subscriptions/packages/edit.breadcrumb.packages')}}</span>
    <span class="breadcrumb-item active">{{__('pages/subscriptions/packages/edit.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/subscriptions/packages/edit.content.title')}}</h5>
        </div>
        @livewire('subscriptions.packages.packages-edit-component', ['id' => $id])
    </div>
@endsection
