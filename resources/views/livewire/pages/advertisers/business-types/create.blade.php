<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name_en">{{__('pages/advertisers/business-types/create.content.inputs.name_en')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en" wire:model.defer="name_en"/>
                @error('name_en')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name_ar">{{__('pages/advertisers/business-types/create.content.inputs.name_ar')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" wire:model.defer="name_ar"/>
                @error('name_ar')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="is_active">{{__('pages/advertisers/business-types/create.content.inputs.is_active')}}</label>
            <div class="col-lg-10">
                <select class="form-control @error('is_active') is-invalid @enderror" id="is_active" wire:mode.defer="is_active">
                    <option value="1">{{__('pages/advertisers/business-types/create.content.inputs.boolean.yes')}}</option>
                    <option value="0">{{__('pages/advertisers/business-types/create.content.inputs.boolean.no')}}</option>
                </select>
                @error('is_active')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/advertisers/business-types/create.content.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>
