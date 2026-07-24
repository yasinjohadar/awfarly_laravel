@extends('admin.layouts.app')

@section('title', __('pages/community/comments/reports/reports.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/community/comments/reports/reports.breadcrumb.community')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/comments/reports/reports.breadcrumb.comments')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/comments/reports/reports.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('advertisements.comments.reports.advertisements-reported-comments-component')
@endsection
