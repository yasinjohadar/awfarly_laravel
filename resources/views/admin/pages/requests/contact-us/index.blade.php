@extends('admin.layouts.app')

@section('title', __('pages/requests/contact-us/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/requests/contact-us/index.breadcrumb.requests')}}</span>
    <span class="breadcrumb-item active">{{__('pages/requests/contact-us/index.breadcrumb.contact-us')}}</span>
    <span class="breadcrumb-item active">{{__('pages/requests/contact-us/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('requests.contact-us.requests-contact-us-component')
@endsection
