@extends('admin.layouts.app')

@section('title', __('pages/community/offers/comments/reports/reports.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/community/offers/comments/reports/reports.breadcrumb.community')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/offers/comments/reports/reports.breadcrumb.comments')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/offers/comments/reports/reports.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('community.offers.comments.reports.community-reported-offers-comments-component')
@endsection
