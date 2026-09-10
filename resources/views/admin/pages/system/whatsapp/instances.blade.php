@extends('admin.layouts.app')

@section('title', __('pages/system/whatsapp.breadcrumb.instances'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/system/whatsapp.breadcrumb.system')}}</span>
    <span class="breadcrumb-item active">{{__('pages/system/whatsapp.breadcrumb.instances')}}</span>
@endsection

@section('content')
    @include('admin.pages.system.whatsapp.partials.nav')
    @livewire('system.whats-app.whats-app-instances-component')
@endsection
