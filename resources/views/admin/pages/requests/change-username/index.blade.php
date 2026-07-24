@extends('admin.layouts.app')

@section('title', __('pages/requests/username-change/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/requests/username-change/index.breadcrumb.requests')}}</span>
    <span class="breadcrumb-item active">{{__('pages/requests/username-change/index.breadcrumb.contact-us')}}</span>
    <span class="breadcrumb-item active">{{__('pages/requests/username-change/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('requests.username-change.requests-username-change-component')
@endsection
