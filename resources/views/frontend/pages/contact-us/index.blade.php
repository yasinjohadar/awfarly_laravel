@extends('frontend.layouts.app')

@section('title', __('frontend/contact-us/contact-us.breadcrumb.title'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h1>{{__('frontend/contact-us/contact-us.breadcrumb.title')}}</h1>
        </div>
        <div class="card-body">
            @livewire('frontend.contact-us.contact-us-component', ['type' => $type])
        </div>
    </div>
@endsection
