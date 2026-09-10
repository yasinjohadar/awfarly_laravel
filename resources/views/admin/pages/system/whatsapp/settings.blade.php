@extends('admin.layouts.app')

@section('title', __('pages/system/whatsapp.breadcrumb.settings'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/system/whatsapp.breadcrumb.system')}}</span>
    <span class="breadcrumb-item active">{{__('pages/system/whatsapp.breadcrumb.settings')}}</span>
@endsection

@section('content')
    @include('admin.pages.system.whatsapp.partials.nav')
    @livewire('system.whats-app.whats-app-settings-component')
@endsection
