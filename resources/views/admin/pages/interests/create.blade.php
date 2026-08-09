@extends('admin.layouts.app')

@section('title', __('pages/interests/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/interests/create.breadcrumb.interests')}}</div>
    <div class="breadcrumb-item active">{{__('pages/interests/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/interests/create.content.title')}}</h5>
        </div>
        @livewire('interests.interests-create-component')
    </div>
@endsection
