@extends('admin.layouts.app')

@section('title', __('pages/community/proposals/reports/reports.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/community/proposals/reports/reports.breadcrumb.community')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/proposals/reports/reports.breadcrumb.proposals')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/proposals/reports/reports.breadcrumb.reports')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/proposals/reports/reports.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('community.proposals.reports.community-reported-proposals-component')
@endsection
