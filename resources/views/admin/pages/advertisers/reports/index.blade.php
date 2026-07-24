@extends('admin.layouts.app')

@section('title', __('pages/advertisers/reports/reports.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisers/reports/reports.breadcrumb.advertisers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/reports/reports.breadcrumb.reports')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/reports/reports.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('advertisers.reports.reported-advertisers-component')
@endsection
