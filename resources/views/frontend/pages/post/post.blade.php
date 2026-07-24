@extends('frontend.layouts.app')

@section('title', __('frontend/post/post.breadcrumb.title'))

@section('content')
    @livewire('frontend.community.posts.community-post-component', ['post_id' => $post_id])
@endsection
