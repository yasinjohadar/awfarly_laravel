@extends('admin.layouts.app')

@section('title', __('pages/community/chats/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/community/chats/index.breadcrumb.community')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/chats/index.breadcrumb.chats')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/chats/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('community.chats.community-chats-component')
@endsection
