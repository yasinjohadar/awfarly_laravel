@extends('admin.layouts.app')

@section('title', __('pages/community/comments/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisements/index.breadcrumb.advertisements')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/comments/index.breadcrumb.comments')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/comments/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/community/comments/index.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('advertisements.comments.advertisements-comments-component', ['filter_id' => $filter_id ?? null])
        </div>
    </div>
@endsection
