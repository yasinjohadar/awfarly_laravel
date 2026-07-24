@extends('frontend.layouts.app')

@section('title', __('frontend/offer/offer.breadcrumb.title'))

@section('content')
    @livewire('frontend.community.offers.community-offer-component', ['offer_id' => $offer_id])
@endsection
