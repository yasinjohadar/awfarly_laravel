<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="name_en">{{__('pages/interests/create.content.inputs.name_en')}}</label>
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
                   for="name_ar">{{__('pages/interests/create.content.inputs.name_ar')}}</label>
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
        <div class="form-group row">
            <label for="image"
                   class="col-form-label col-lg-2">{{__('pages/interests/create.content.inputs.placeholders.choose_file')}}</label>
            <div class="col-lg-10">
                <input type="file" wire:model.defer="image" class="form-control h-auto" id="image">
                @error('image') <span class="error">{{ $message }}</span> @enderror
            </div>
            @if($image)
                <img alt="{{$image}}" class="img-fluid mt-2" width="240" src="{{ $image->temporaryUrl() }}">
            @endif
        </div>
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/interests/create.content.submit')}}
            </x-primary-button>
        </div>
    </form>
</div>


@push('scripts')
    <script type="text/javascript">
        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#image').val(null);
        });
    </script>
@endpush
