@extends('admin.layouts.app')

@section('title', __('pages/interests/index.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/interests/index.breadcrumb.interests')}}</span>
    <span class="breadcrumb-item active">{{__('pages/interests/index.breadcrumb.page')}}</span>
@endsection

@section('content')
    @livewire('interests.interests-component')
@endsection
