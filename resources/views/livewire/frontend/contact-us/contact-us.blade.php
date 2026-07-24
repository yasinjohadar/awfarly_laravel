<div>
    <form wire:submit.prevent="store">
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="type">{{__('frontend/contact-us/contact-us.inputs.name')}}</label>
            <div class="col-lg-10">
                <select class="form-control @error('type') is-invalid @enderror" id="type" wire:model.defer="type">
                    <option value="Enquiry">{{__('frontend/contact-us/contact-us.inputs.types.Enquiry')}}</option>
                    <option value="Complaint">{{__('frontend/contact-us/contact-us.inputs.types.Complaint')}}</option>
                    <option value="Suggestion">{{__('frontend/contact-us/contact-us.inputs.types.Suggestion')}}</option>
                    <option value="Payments">{{__('frontend/contact-us/contact-us.inputs.types.Payments')}}</option>
                    <option value="Technical support">{{__('frontend/contact-us/contact-us.inputs.types.Technical support')}}</option>
                    <option value="In-app advertising">{{__('frontend/contact-us/contact-us.inputs.types.In-app advertising')}}</option>
                    <option value="Report a problem">{{__('frontend/contact-us/contact-us.inputs.types.Report a problem')}}</option>
                </select>
                @error('type')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name">{{__('frontend/contact-us/contact-us.inputs.name')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                       wire:model.defer="name">
                @error('name')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="mobile">{{__('frontend/contact-us/contact-us.inputs.mobile')}}</label>
            <div class="col-lg-10">
                <input type="text" class="form-control @error('mobile') is-invalid @enderror" id="mobile"
                       wire:model.defer="mobile"/>
                @error('mobile')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="whatsapp_mobile">{{__('frontend/contact-us/contact-us.inputs.whatsapp_mobile')}}</label>
            <div class="col-lg-10">
                <input class="form-control @error('whatsapp_mobile') is-invalid @enderror" id="whatsapp_mobile"
                       wire:model.defer="whatsapp_mobile">
                @error('whatsapp_mobile')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="email">{{__('frontend/contact-us/contact-us.inputs.email')}}</label>
            <div class="col-lg-10">
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                       wire:model.defer="email"/>
                @error('email')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="message">{{__('frontend/contact-us/contact-us.inputs.message')}}</label>
            <div class="col-lg-10">
                <textarea type="message" class="form-control @error('message') is-invalid @enderror" id="message"
                          wire:model.defer="message"></textarea>
                @error('message')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('frontend/contact-us/contact-us.inputs.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>
