@extends('admin.layouts.app')

@section('title', __('pages/system/firebase.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/system/firebase.breadcrumb.system')}}</span>
    <span class="breadcrumb-item active">{{__('pages/system/firebase.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('system.firebase.firebase-settings-component')
@endsection
