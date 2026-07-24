<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="code">{{__('pages/countries/create.content.inputs.code')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                       name="code" wire:model.defer="code" maxlength="2" minlength="2" style="text-transform:uppercase"
                       placeholder="{{__('pages/countries/create.content.inputs.code_placeholder')}}"/>
                <div class="form-control-plaintext p-0">
                    {{__('pages/countries/create.content.inputs.code_notes')}} <a href="https://www.iban.com/country-codes"
                       target="_blank">{{__('pages/countries/create.content.inputs.click_here')}}</a>.
                </div>
                @error('code')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name_en">{{__('pages/countries/create.content.inputs.name_en')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en"
                       name="name_en" wire:model.defer="name_en"
                />
                @error('name_en')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name_ar">{{__('pages/countries/create.content.inputs.name_ar')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar"
                       name="name_ar" wire:model.defer="name_ar"
                />
                @error('name_ar')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        {{--<div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="mobile_code">{{__('pages/countries/create.content.inputs.mobile_code')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('mobile_code') is-invalid @enderror" id="mobile_code"
                       name="mobile_code" wire:model.defer="mobile_code"
                       />
                @error('mobile_code')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>--}}
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/countries/create.content.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>
