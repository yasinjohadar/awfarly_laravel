@extends('admin.layouts.app')

@section('title', __('pages/currencies/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/currencies/index.breadcrumb.currencies')}}</span>
    <span class="breadcrumb-item active">{{__('pages/currencies/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="card-title mb-0">{{__('pages/currencies/index.content.title')}}</h5>
            @can('currencies.add')
                <a href="{{route('admin.currencies.create')}}" class="btn btn-primary">
                    {{__('pages/currencies/index.content.add')}}
                </a>
            @endcan
        </div>
        <div class="card-body">
            @livewire('currencies.currencies-inquiry-component')
        </div>
    </div>
@endsection
