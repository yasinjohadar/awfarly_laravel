@extends('admin.layouts.app')

@section('title', __('pages/categories/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/categories/create.breadcrumb.categories')}}</div>
    <div class="breadcrumb-item active">{{__('pages/categories/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/categories/create.content.title')}}</h5>
        </div>
        @livewire('categories.categories-create-component')
    </div>
@endsection
