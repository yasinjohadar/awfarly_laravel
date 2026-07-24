@extends('admin.layouts.app')

@section('title', __('pages/advertisements/side/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/advertisements/side/create.breadcrumb.advertisements')}}</div>
    <div class="breadcrumb-item active">{{__('pages/advertisements/side/create.breadcrumb.side')}}</div>
    <div class="breadcrumb-item active">{{__('pages/advertisements/side/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/advertisements/side/create.content.title')}}</h5>
        </div>
        @livewire('advertisements.side.side-advertisements-create-component')
    </div>
@endsection
