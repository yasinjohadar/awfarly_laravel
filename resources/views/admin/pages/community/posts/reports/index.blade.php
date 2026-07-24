@extends('admin.layouts.app')

@section('title', __('pages/community/posts/reports/reports.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/community/posts/reports/reports.breadcrumb.community')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/posts/reports/reports.breadcrumb.posts')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/posts/reports/reports.breadcrumb.reports')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/posts/reports/reports.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('community.posts.reports.community-reported-posts-component')
@endsection
