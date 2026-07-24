@extends('admin.layouts.app')

@section('title', __('pages/marketing-tools/sms.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/marketing-tools/sms.breadcrumb.marketing-tools')}}</span>
    <span class="breadcrumb-item active">{{__('pages/marketing-tools/sms.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/marketing-tools/sms.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('marketing-tools.send-sms-component')
        </div>
    </div>
@endsection
