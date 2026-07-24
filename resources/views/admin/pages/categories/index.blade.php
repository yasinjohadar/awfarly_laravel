@extends('admin.layouts.app')

@section('title', __('pages/categories/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/categories/index.breadcrumb.categories')}}</span>
    <span class="breadcrumb-item active">{{__('pages/categories/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('categories.categories-component')
@endsection
