@extends('admin.layouts.app')

@section('title', __('pages/admins/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/admins/create.breadcrumb.admins')}}</div>
    <div class="breadcrumb-item active">{{__('pages/admins/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/admins/create.content.title')}}</h5>
        </div>
        @livewire('admins.admins-create-component')
    </div>
@endsection
