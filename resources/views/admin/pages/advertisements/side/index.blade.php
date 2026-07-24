@extends('admin.layouts.app')

@section('title', __('pages/advertisements/side/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisements/side/index.breadcrumb.advertisements')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisements/side/index.breadcrumb.side')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisements/side/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('advertisements.side.side-advertisements-component')
@endsection
