@extends('admin.layouts.app')

@section('title', __('pages/community/offers/ratings/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/community/offers/ratings/index.breadcrumb.offers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/offers/ratings/index.breadcrumb.ratings')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/offers/ratings/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('community.offers.ratings.community-offers-ratings-component')
@endsection
