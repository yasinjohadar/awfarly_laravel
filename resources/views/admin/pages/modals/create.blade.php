@extends('admin.layouts.app')

@section('title', __('pages/modals/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/modals/create.breadcrumb.modals')}}</div>
    <div class="breadcrumb-item active">{{__('pages/modals/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/modals/create.content.title')}}</h5>
        </div>
        @livewire('modals.modals-create-component')
    </div>
@endsection
