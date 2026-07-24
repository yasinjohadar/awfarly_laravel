@extends('admin.layouts.app')

@section('title', __('pages/advertisers/ratings/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/advertisers/ratings/index.breadcrumb.advertisers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/ratings/index.breadcrumb.ratings')}}</span>
    <span class="breadcrumb-item active">{{__('pages/advertisers/ratings/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('advertisers.ratings.advertisers-ratings-component')
@endsection
