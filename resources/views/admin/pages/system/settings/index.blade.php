@extends('admin.layouts.app')

@section('title', __("pages/system/settings/settings.breadcrumb.titles.$settingType"))

@section('breadcrumbs')
    <span class="breadcrumb-item active">{{__('pages/system/settings/settings.breadcrumb.system')}}</span>
    <span class="breadcrumb-item active">{{__('pages/system/settings/settings.breadcrumb.settings')}}</span>
    <span class="breadcrumb-item active">{{__("pages/system/settings/settings.breadcrumb.settingsTypes.$settingType")}}</span>
    <span class="breadcrumb-item active">{{__('pages/system/settings/settings.breadcrumb.page')}}</span>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__("pages/system/settings/settings.content.titles.$settingType")}}</h5>
        </div>
        <div class="card-body">
            @livewire('system.settings.settings-inquiry-component', ['type' => $settingType])
        </div>
    </div>
@endsection
