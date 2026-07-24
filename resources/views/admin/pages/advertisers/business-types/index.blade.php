@extends('admin.layouts.app')

@section('title', __('pages/advertisers/business-types/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisers/business-types/index.breadcrumb.advertisers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/business-types/index.breadcrumb.business_types')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/business-types/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/advertisers/business-types/index.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('advertisers.business-types.business-types-inquiry-component')
        </div>
    </div>
@endsection
