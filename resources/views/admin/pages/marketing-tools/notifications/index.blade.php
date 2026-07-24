@extends('admin.layouts.app')

@section('title', __('pages/marketing-tools/notifications.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/marketing-tools/notifications.breadcrumb.marketing-tools')}}</span>
    <span class="breadcrumb-item active">{{__('pages/marketing-tools/notifications.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/marketing-tools/notifications.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('marketing-tools.send-notifications-component')
        </div>
    </div>
@endsection
