@extends('admin.layouts.app')

@section('title', __('pages/system/logs/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/system/logs/index.breadcrumb.system')}}</span>
    <span class="breadcrumb-item active">{{__('pages/system/logs/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/system/logs/index.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('system.logs.system-logs-component')
        </div>
    </div>
@endsection
