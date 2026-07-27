@extends('admin.layouts.app')

@section('title', __('pages/advertisers/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisers/index.breadcrumb.advertisers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="card-title mb-0">{{__('pages/advertisers/index.content.title')}}</h5>
            <div class="d-flex flex-wrap" style="gap: .5rem;">
                @can('advertisers.inquiry')
                    <a href="{{route('admin.advertisers.reports')}}" class="btn btn-secondary">
                        {{__('pages/advertisers/index.content.reported')}}
                    </a>
                @endcan
                @can('advertisers.add')
                    <a href="{{route('admin.advertisers.create')}}" class="btn btn-primary">
                        {{__('pages/advertisers/index.content.add')}}
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            @livewire('advertisers.advertisers-inquiry-component', ['activeNumberFilters' => $activeNumberFilters ?? []])
        </div>
    </div>
@endsection
