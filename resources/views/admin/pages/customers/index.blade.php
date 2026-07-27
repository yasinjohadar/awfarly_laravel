@extends('admin.layouts.app')

@section('title', __('pages/customers/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/customers/index.breadcrumb.customers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/customers/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="card-title mb-0">{{__('pages/customers/index.content.title')}}</h5>
            <div class="d-flex flex-wrap" style="gap: .5rem;">
                @can('customers.inquiry')
                    <a href="{{route('admin.customers.reports')}}" class="btn btn-secondary">
                        {{__('pages/customers/index.content.reported')}}
                    </a>
                @endcan
                @can('customers.add')
                    <a href="{{route('admin.customers.create')}}" class="btn btn-primary">
                        {{__('pages/customers/index.content.add')}}
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            @livewire('customers.customers-inquiry-component', ['activeNumberFilters' => $activeNumberFilters ?? []])
        </div>
    </div>
@endsection
