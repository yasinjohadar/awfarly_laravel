@extends('admin.layouts.app')

@section('title', __('pages/modals/inquiry.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/modals/inquiry.breadcrumb.title')}}</span>
    <span class="breadcrumb-item active">{{__('pages/modals/inquiry.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('modals.modals-component')
@endsection
