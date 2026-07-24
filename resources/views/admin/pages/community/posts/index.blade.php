@extends('admin.layouts.app')

@section('title', __('pages/community/posts/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/community/posts/index.breadcrumb.community')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/posts/index.breadcrumb.posts')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/posts/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('community.posts.community-posts-component', ['filter_id' => $filter_id ?? null])
@endsection
