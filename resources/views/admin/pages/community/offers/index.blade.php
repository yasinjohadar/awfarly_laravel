@extends('admin.layouts.app')

@section('title', __('pages/community/offers/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/community/offers/index.breadcrumb.community')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/offers/index.breadcrumb.offers')}}</span>
    <span class="breadcrumb-item active">{{__('pages/community/offers/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('community.offers.community-offers-component', ['filter_id' => $filter_id ?? null])
@endsection
