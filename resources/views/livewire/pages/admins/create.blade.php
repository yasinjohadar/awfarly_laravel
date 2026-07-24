<div class="card-body">
    <form wire:submit.prevent="store">
        <div class="form-group row">
            <label class="col-form-label col-lg-1" for="name">{{__('pages/admins/create.content.inputs.name')}}</label>
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
        <div wire:ignore class="form-group row" x-data="{admin_roles: @entangle('admin_roles').defer}"
             x-init="$nextTick(() => {select2 = $($refs.select).select2({multiple: true,}).val(admin_roles).change();select2.on('select2:select', (event) => {admin_roles = $('#admin_roles').val();});select2.on('select2:unselect', (event) => {admin_roles = $('#admin_roles').val();});})">
            <label class="col-form-label col-lg-1"
                   for="admin_roles">{{__('pages/admins/create.content.inputs.roles')}}</label>
            <div class="col-lg-11">
                <select x-model="admin_roles" multiple
                        id="admin_roles"
                        class="form-control select2 @error('admin_roles') is-invalid @enderror"
                        x-ref="select"
                        x-bind:value="admin_roles">
                    @foreach($roles as $role)
                        <option value="{{$role['id']}}">{{$role['name']}}</option>
                    @endforeach
                </select>
                @error('admin_roles')
                <div class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row">
            <label class="col-form-label col-lg-1"
                   for="email">{{__('pages/admins/create.content.inputs.email')}}</label>
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
                   for="mobile">{{__('pages/admins/create.content.inputs.mobile')}}</label>
            <div class="col-lg-11">
                <input type="text" dir="ltr"
                       class="form-control{{app()->getLocale() === 'ar' ? ' text-left' : ''}} @error('mobile') is-invalid @enderror"
                       id="mobile"
                       name="mobile" wire:model.defer="mobile"/>
                @error('mobile')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
            </div>
        </div>
        <div class="form-group row" x-data="{ isUploading: false, progress: 0, isUploaded: false }"
             x-on:livewire-upload-start="isUploading = true; isUploaded = false;"
             x-on:livewire-upload-finish="isUploading = false; isUploaded = true;"
             x-on:livewire-upload-error="isUploading = false; isUploaded = false;"
             x-on:livewire-upload-progress="progress = $event.detail.progress">
            <label for="image"
                   class="col-form-label col-lg-1">{{__('pages/admins/create.content.inputs.placeholders.choose_file')}}</label>
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
                   for="username">{{__('pages/admins/create.content.inputs.username')}}</label>
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
                   for="language">{{__('pages/admins/create.content.inputs.language')}}</label>
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
                   for="password">{{__('pages/admins/create.content.inputs.password')}}</label>
            <div class="col-lg-11">
                <input class="form-control" id="password" name="password" wire:model.defer="password">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-lg-2"
                   for="status">{{__('pages/admins/create.content.inputs.status')}}</label>
            <div class="col-lg-10">
                <x-select wire:model.defer="status"
                          :options="[
                            'active' => __('pages/admins/create.content.inputs.status_options.active'),
                            'inactive' => __('pages/admins/create.content.inputs.status_options.inactive'),
                            'banned' => __('pages/admins/create.content.inputs.status_options.banned')
                            ]"
                          id="status"></x-select>
                @error('status')
                <div class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
                @enderror
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
    <script type="text/javascript">
        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#image').val(null);
            $('#admin_roles').select2({multiple: true,}).val(null).change();
        });
    </script>
@endpush
