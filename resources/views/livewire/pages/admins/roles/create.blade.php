<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="name">{{__('pages/admins/roles/create.content.inputs.name')}}</label>
            <div class="col-lg-11">
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                       name="name" wire:model.defer="name">
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
                <div class="transfer" id="permissions"></div>
            </div>
        </div>
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/admins/create.content.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        let groupsData = {!! json_encode($groups) !!};
        let settings = {
            "tabNameText": "{{__('pages/admins/roles/create.content.inputs.permissions')}}",
            "rightTabNameText": "{{__('pages/admins/roles/create.content.inputs.selected')}}",
            "searchPlaceholderText": "{{__('pages/admins/roles/create.content.inputs.placeholders.search')}}",
            "groupDataArray": groupsData,
            "groupItemName": "groupName",
            "groupArrayName": "groupData",
            "itemName": "permission",
            "valueName": "value",
        };
        let myTransfer = $("#permissions").transfer(settings);
    </script>
@endpush
