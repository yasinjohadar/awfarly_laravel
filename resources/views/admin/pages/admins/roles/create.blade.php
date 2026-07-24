@extends('admin.layouts.app')

@section('title', __('pages/admins/roles/create.breadcrumb.title'))

@section('breadcrumbs')
    <div class="breadcrumb-item active">{{__('pages/admins/roles/create.breadcrumb.admins')}}</div>
    <div class="breadcrumb-item active">{{__('pages/admins/roles/create.breadcrumb.roles')}}</div>
    <div class="breadcrumb-item active">{{__('pages/admins/roles/create.breadcrumb.page')}}</div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">{{__('pages/admins/roles/create.content.title')}}</h5>
        </div>
        <div class="card-body">
            @include('globals.messages')
            <form action="{{route('admin.roles.store')}}" method="post" enctype="multipart/form-data"
                  autocomplete="off">
                @csrf
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="name">{{__('pages/admins/roles/create.content.inputs.name')}}</label>
                    <div class="col-lg-11">
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                               name="name" value="{{old('name')}}" required>
                        @error('name')
                        <div class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-lg-1"
                           for="name">{{__('pages/admins/roles/create.content.inputs.permissions')}}</label>
                    <div class="col-lg-11">
                        @error('permissions')
                        <div class="invalid-feedback d-block mb-2" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                        <div class="transfer" id="permissions"></div>
                    </div>
                </div>
                <hr>
                <div class="text-right">
                    <x-primary-button type="submit">
                        {{__('pages/admins/roles/create.content.submit')}}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        let groupsData = {!! json_encode($groups) !!};
        let settings = {
            "tabNameText": "{{__('pages/admins/roles/create.content.inputs.permissions')}}",
            "rightTabNameText": "{{__('pages/admins/roles/create.content.inputs.selected')}}",
            "searchPlaceholderText": "{{__('pages/admins/roles/create.content.inputs.placeholders.search')}}",
            "include": "{{__('pages/admins/roles/create.content.inputs.include')}}",
            "exclude": "{{__('pages/admins/roles/create.content.inputs.exclude')}}",
            "groupDataArray": groupsData,
            "groupItemName": "groupName",
            "groupArrayName": "groupData",
            "itemName": "permission",
            "valueName": "value",
        };
        let myTransfer = $("#permissions").transfer(settings);
    </script>
@endpush
