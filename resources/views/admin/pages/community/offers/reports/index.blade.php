@extends('admin.layouts.app')

@section('title', __('pages/community/offers/reports/reports.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/community/offers/reports/reports.breadcrumb.community')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/offers/reports/reports.breadcrumb.offers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/offers/reports/reports.breadcrumb.reports')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/offers/reports/reports.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('community.offers.reports.community-reported-offers-component')
@endsection
