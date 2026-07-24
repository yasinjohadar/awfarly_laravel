@extends('admin.layouts.app')

@section('title', __('pages/marketing-tools/emails.breadcrumb.title'))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/marketing-tools/emails.breadcrumb.marketing-tools')}}</span>
    <span class="breadcrumb-item active">{{__('pages/marketing-tools/emails.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/marketing-tools/emails.content.title')}}</h5>
        </div>
        <div class="card-body">
            @livewire('marketing-tools.send-emails-component')
        </div>
    </div>
@endsection
