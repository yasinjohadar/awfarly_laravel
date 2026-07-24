@extends('admin.layouts.app')

@section('title', __('pages/advertisers/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/advertisers/create.breadcrumb.advertisers')}}</div>
    <div class="breadcrumb-item active">{{__('pages/advertisers/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/advertisers/create.content.title')}}</h5>
        </div>
        @livewire('advertisers.advertisers-create-component')
    </div>
@endsection
