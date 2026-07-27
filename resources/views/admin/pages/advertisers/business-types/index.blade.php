@extends('admin.layouts.app')

@section('title', __('pages/advertisers/business-types/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisers/business-types/index.breadcrumb.advertisers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/business-types/index.breadcrumb.business_types')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/business-types/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="card-title mb-0">{{__('pages/advertisers/business-types/index.content.title')}}</h5>
            @can('business.types.add')
                <a href="{{route('admin.advertisers.business.types.create')}}" class="btn btn-primary">
                    {{__('pages/advertisers/business-types/index.content.add')}}
                </a>
            @endcan
        </div>
        <div class="card-body">
            @livewire('advertisers.business-types.business-types-inquiry-component')
        </div>
    </div>
@endsection
