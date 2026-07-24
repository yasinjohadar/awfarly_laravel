@extends('admin.layouts.app')

@section('title', __('pages/advertisements/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisements/index.breadcrumb.advertisements')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisements/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('advertisements.advertisements-component')
@endsection
