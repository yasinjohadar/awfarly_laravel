@extends('admin.layouts.app')

@section('title', __('pages/account/edit.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/account/edit.breadcrumb.account')}}</span>
    <span class="breadcrumb-item active">{{__('pages/account/edit.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/account/edit.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('account.account-component')
        </div>
    </div>
@endsection
