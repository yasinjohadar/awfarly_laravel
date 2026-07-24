<div class="card-body">
    <form wire:submit.prevent="update">
        <div class="form-group row">
            <label class="col-form-label col-lg-1" for="name">{{__('pages/account/edit.content.name')}}</label>
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
                   for="email">{{__('pages/account/edit.content.email')}}</label>
            <div class="col-lg-11">
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                       name="email" wire:model.defer="email"/>
                @error('email')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="mobile">{{__('pages/account/edit.content.mobile')}}</label>
            <div class="col-lg-11">
                <input type="text" class="form-control @error('mobile') is-invalid @enderror" id="mobile"
                       name="mobile" wire:model.defer="mobile"/>
                @error('mobile')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label for="image"
                   class="col-form-label col-lg-1">{{__('pages/account/edit.content.image')}}</label>
            <div class="col-lg-11" x-data="{ isUploading: false, progress: 0, isUploaded: false }"
                 x-on:livewire-upload-start="isUploading = true; isUploaded = false;"
                 x-on:livewire-upload-finish="isUploading = false; isUploaded = true;"
                 x-on:livewire-upload-error="isUploading = false; isUploaded = false;"
                 x-on:livewire-upload-progress="progress = $event.detail.progress">

                <input type="file" wire:model.defer="new_image" class="form-control h-auto" id="image"/>

                <!-- Progress Bar -->
                <div x-show="isUploading">
                    <progress max="100" x-bind:value="progress"></progress>
                </div>
                @if($new_image)
                    <div x-show="isUploaded">
                        <img alt="{{$new_image}}" class="img-fluid mt-2" width="240"
                             src="{{ $new_image->temporaryUrl() }}">
                    </div>
                @endif
                @error('new_image') <span class="error">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="username">{{__('pages/account/edit.content.username')}}</label>
            <div class="col-lg-11">
                <input class="form-control @error('username') is-invalid @enderror" id="username"
                       name="username" wire:model.defer="username">
                @error('username')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="language">{{__('pages/account/edit.content.language')}}</label>
            <div class="col-lg-11">
                <x-select wire:model.defer="language_code" :options="$languages" id="language"></x-select>
                @error('language_code')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="password">{{__('pages/account/edit.content.password')}}</label>
            <div class="col-lg-11">
                <input class="form-control @error('password') is-invalid @enderror" id="password" name="password"
                       wire:model.defer="password" type="password">
                <legend>{{__('pages/account/edit.content.passwordNote')}}</legend>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="password_confirmation">{{__('pages/account/edit.content.passwordConfirmation')}}</label>
            <div class="col-lg-11">
                <input class="form-control @error('password_confirmation') is-invalid @enderror"
                       id="password_confirmation" name="password_confirmation" wire:model.defer="password_confirmation" type="password">
                <legend>{{__('pages/account/edit.content.passwordConfirmationNote')}}</legend>

                @error('password_confirmation')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <hr>

        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="old_password">{{__('pages/account/edit.content.currentPassword')}}</label>
            <div class="col-lg-11">
                <input class="form-control @error('old_password') is-invalid @enderror" id="old_password"
                       name="old_password" wire:model.defer="old_password" type="password">
                <legend>{{__('pages/account/edit.content.currentPasswordNote')}}</legend>

                @error('old_password')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <hr>
        <div class="text-right">
            <x-primary-button type="submit">
                {{__('pages/account/edit.content.submit')}}
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
