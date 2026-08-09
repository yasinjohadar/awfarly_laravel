<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="row">
            <div class="form-group col-md-6">
                <label for="code">{{__('pages/currencies/create.content.inputs.code')}}</label>
                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                       name="code" wire:model.defer="code" style="text-transform:uppercase"
                       placeholder="{{__('pages/currencies/create.content.inputs.code_placeholder')}}"/>
                @error('code')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="symbol">{{__('pages/currencies/create.content.inputs.symbol')}}</label>
                <input type="text" class="form-control @error('symbol') is-invalid @enderror" id="symbol"
                       name="symbol" wire:model.defer="symbol"/>
                @error('symbol')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="name_en">{{__('pages/currencies/create.content.inputs.name_en')}}</label>
                <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en"
                       name="name_en" wire:model.defer="name_en"/>
                @error('name_en')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="name_ar">{{__('pages/currencies/create.content.inputs.name_ar')}}</label>
                <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar"
                       name="name_ar" wire:model.defer="name_ar"/>
                @error('name_ar')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="exchange_rate">{{__('pages/currencies/create.content.inputs.exchange_rate')}}</label>
                <input type="number" min="0.000001" step="0.000001" class="form-control @error('exchange_rate') is-invalid @enderror"
                       id="exchange_rate" wire:model.defer="exchange_rate"/>
                <div class="form-control-plaintext p-0">
                    {{__('pages/currencies/create.content.inputs.exchange_rate_notes')}}
                </div>
                @error('exchange_rate')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="is_active">{{__('pages/currencies/create.content.inputs.is_active')}}</label>
                <select class="form-control" wire:model.defer="is_active" id="is_active">
                    <option value="1">{{__('pages/currencies/create.content.inputs.boolean.yes')}}</option>
                    <option value="0">{{__('pages/currencies/create.content.inputs.boolean.no')}}</option>
                </select>
                @error('is_active')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="is_visible">{{__('pages/currencies/create.content.inputs.is_visible')}}</label>
                <select class="form-control" wire:model.defer="is_visible" id="is_visible">
                    <option value="1">{{__('pages/currencies/create.content.inputs.boolean.yes')}}</option>
                    <option value="0">{{__('pages/currencies/create.content.inputs.boolean.no')}}</option>
                </select>
                <div class="form-control-plaintext p-0">
                    {{__('pages/currencies/create.content.inputs.is_visible_notes')}}
                </div>
                @error('is_visible')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/currencies/create.content.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>
