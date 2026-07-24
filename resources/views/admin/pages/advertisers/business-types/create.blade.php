@extends('admin.layouts.app')

@section('title', __('pages/advertisers/business-types/create.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisers/business-types/create.breadcrumb.advertisers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/business-types/create.breadcrumb.business_types')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/business-types/create.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/advertisers/business-types/create.content.title')}}</h5>
        </div>
        @livewire('advertisers.business-types.business-types-create-component')
    </div>
@endsection
