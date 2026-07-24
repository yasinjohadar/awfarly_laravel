@extends('admin.layouts.app')

@section('title', __('pages/admins/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/admins/index.breadcrumb.admins')}}</span>
    <span class="breadcrumb-item active">{{__('pages/admins/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/admins/index.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('admins.roles.admins-roles-inquiry-component')
        </div>
    </div>
@endsection
