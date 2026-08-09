@extends('admin.layouts.app')

@section('title', __('pages/currencies/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/currencies/create.breadcrumb.currencies')}}</div>
    <div class="breadcrumb-item active">{{__('pages/currencies/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/currencies/create.content.title')}}</h5>
        </div>
        @livewire('currencies.currencies-create-component')
    </div>
@endsection
