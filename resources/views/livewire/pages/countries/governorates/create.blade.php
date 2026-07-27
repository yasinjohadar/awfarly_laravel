<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="country_code">{{__('pages/countries/governorates/create.content.inputs.country_code')}}</label>
            <div class="col-lg-10">
                <select class="form-control @error('country_code') is-invalid @enderror" wire:model.defer="country_code" id="country_code">
                    <option value="">{{__('pages/countries/governorates/create.content.inputs.placeholders.select_code')}}</option>
                    @foreach($countries as $country)
                        <option value="{{$country['country_code']}}">{{$country['name']}}</option>
                    @endforeach
                </select>
                @error('country_code')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name_en">{{__('pages/countries/governorates/create.content.inputs.name_en')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en"
                       name="name_en" wire:model.defer="name_en">
                @error('name_en')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name_ar">{{__('pages/countries/governorates/create.content.inputs.name_ar')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar"
                       name="name_ar" wire:model.defer="name_ar">
                @error('name_ar')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/countries/governorates/create.content.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>
