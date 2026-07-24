@extends('admin.layouts.app')

@section('title', __('pages/pages/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/pages/index.breadcrumb.pages')}}</span>
    <span class="breadcrumb-item active">{{__('pages/pages/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('pages.pages-component')
@endsection
