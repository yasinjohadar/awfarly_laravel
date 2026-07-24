<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="form-group row">
            <label class="col-form-label col-lg-1" for="advertisement_url">{{__('pages/advertisements/side/create.content.inputs.url')}}</label>
            <div class="col-lg-11">
                <input type="url" class="form-control @error('advertisement_url') is-invalid @enderror" id="advertisement_url"
                       name="advertisement_url" wire:model.defer="advertisement_url">
                @error('advertisement_url')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1" for="side">{{__('pages/advertisements/side/create.content.inputs.side.title')}}</label>
            <div class="col-lg-11">
                <select wire:model.defer="side" class="form-control @error('side') is-invalid @enderror" id="side">
                    <option value="right">{{__('pages/advertisements/side/create.content.inputs.side.right')}}</option>
                    <option value="left">{{__('pages/advertisements/side/create.content.inputs.side.left')}}</option>
                </select>
                @error('side')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row"
             x-data="{ isUploading: false, progress: 0, isUploaded: false }"
             x-on:livewire-upload-start="isUploading = true; isUploaded = false;"
             x-on:livewire-upload-finish="isUploading = false; isUploaded = true;"
             x-on:livewire-upload-error="isUploading = false; isUploaded = false;"
             x-on:livewire-upload-progress="progress = $event.detail.progress">
            <label for="image"
                   class="col-form-label col-lg-1">{{__('pages/advertisements/side/create.content.inputs.image')}}</label>
            <div class="col-lg-11">
                <input type="file" wire:model.defer="image" class="form-control h-auto" id="image">
                <!-- Progress Bar -->
                <div x-show="isUploading">
                    <progress max="100" x-bind:value="progress"></progress>
                </div>
                @if($image)
                    <div x-show="isUploaded">
                        <img alt="{{$image}}" class="img-fluid mt-2" width="240"
                             src="{{ $image->temporaryUrl() }}">
                    </div>
                @endif
                @error('image') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="starts_at">{{__('pages/advertisements/side/create.content.inputs.starts_at')}}</label>
            <div class="col-lg-11">
                <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at"
                       wire:model.defer="starts_at"/>
                @error('starts_at')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="ends_at">{{__('pages/advertisements/side/create.content.inputs.ends_at')}}</label>
            <div class="col-lg-11">
                <input type="datetime-local" class="form-control @error('ends_at') is-invalid @enderror" id="ends_at"
                       wire:model.defer="ends_at"/>
                @error('ends_at')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/advertisements/side/create.content.submit')}}
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
