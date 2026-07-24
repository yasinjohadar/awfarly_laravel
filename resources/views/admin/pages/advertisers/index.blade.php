@extends('admin.layouts.app')

@section('title', __('pages/advertisers/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisers/index.breadcrumb.advertisers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/advertisers/index.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('advertisers.advertisers-inquiry-component', ['activeNumberFilters' => $activeNumberFilters ?? []])
        </div>
    </div>
@endsection
