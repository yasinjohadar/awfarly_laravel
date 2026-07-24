<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$user['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <form wire:submit.prevent="update({{$user['id'] ?? null}})">
        <x-slot name="content">
            <div class="form-group">
                <label for="name">{{__('pages/admins/index.modal.edit.inputs.name')}}</label>
                <input type="text" class="form-control @error('user.name') is-invalid @enderror" id="name" name="name"
                       wire:model.defer="user.name"
                >
                @error('user.name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            @if(!isset($user['is_super_administrator']) || !$user['is_super_administrator'])
                <div class="form-group" x-data="{admin_roles: @entangle('admin_roles').defer}"
                     x-init="$nextTick(() => {select2 = $($refs.select).select2({multiple: true,}).val(admin_roles).change();select2.on('select2:select', (event) => {admin_roles = $('#admin_roles').val();});select2.on('select2:unselect', (event) => {admin_roles = $('#admin_roles').val();});})">
                    <label for="admin_roles">{{__('pages/admins/index.modal.edit.inputs.roles')}}</label>
                    <select x-model="admin_roles" multiple='multiple'
                            id="admin_roles"
                            @if(isset($user['is_super_administrator']) && $user['is_super_administrator']) disabled
                            @endif
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
            @endif
            <div class="form-group">
                <label for="email">{{__('pages/admins/index.modal.edit.inputs.email')}}</label>
                <input type="email" class="form-control @error('user.email') is-invalid @enderror" id="email"
                       name="email" wire:model.defer="user.email"
                >
                @error('user.email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="mobile">{{__('pages/admins/index.modal.edit.inputs.mobile')}}</label>
                <input type="text" dir="ltr"
                       class="form-control{{app()->getLocale() === 'ar' ? ' text-left' : ''}} @error('user.mobile') is-invalid @enderror"
                       id="mobile"
                       name="mobile" wire:model.defer="user.mobile"
                >
                @error('user.mobile')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group"
                 x-data="{ isUploading: false, progress: 0, isUploaded: false }"
                 x-on:livewire-upload-start="isUploading = true; isUploaded = false;"
                 x-on:livewire-upload-finish="isUploading = false; isUploaded = true;"
                 x-on:livewire-upload-error="isUploading = false; isUploaded = false;"
                 x-on:livewire-upload-progress="progress = $event.detail.progress">
                <label for="new_image">{{__('pages/admins/index.modal.edit.inputs.image')}}</label>
                <input type="file" wire:model.defer="user.new_image" class="form-control h-auto" id="new_image">
                <!-- Progress Bar -->
                <div x-show="isUploading">
                    <progress max="100" x-bind:value="progress"></progress>
                </div>
                @error('user.new_image') <span class="error">{{ $message }}</span> @enderror
                @isset($user['new_image'])
                    <img alt="{{$user['new_image']}}" class="img-fluid mt-2" width="240"
                         src="{{ $user['new_image']->temporaryUrl() }}">
                @endisset
            </div>
            <div class="form-group">
                <label for="username">{{__('pages/admins/index.modal.edit.inputs.username')}}</label>
                <input class="form-control @error('user.username') is-invalid @enderror" id="username" name="username"
                       wire:model.defer="user.username"
                >
                @error('user.username')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="language">{{__('pages/admins/index.modal.edit.inputs.language')}}</label>
                <x-select wire:model.defer="user.language_code" :options="$languages" id="language"></x-select>
                @error('user.language_code')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">{{__('pages/admins/index.modal.edit.inputs.password')}}</label>
                <input class="form-control" id="password" name="password" wire:model.defer="user.password"
                >
                <div
                    class="text-danger small">{{__('pages/admins/index.modal.edit.inputs.placeholders.password')}}</div>
            </div>
            @if(isset($user['is_protected']) && !$user['is_protected'])
                <div class="form-group">
                    <label for="status">{{__('pages/admins/index.modal.edit.inputs.status')}}</label>
                    <x-select wire:model.defer="user.status"
                              :options="[
                                    'active' => __('pages/admins/index.modal.edit.inputs.status_options.active'),
                                    'inactive' => __('pages/admins/index.modal.edit.inputs.status_options.inactive'),
                                    'banned' => __('pages/admins/index.modal.edit.inputs.status_options.banned')
                                ]"
                              id="status"></x-select>
                    @error('user.status')
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                    @enderror
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
                {{ $editModalTexts['cancel'] }}
            </x-secondary-button>

            <x-primary-button wire:loading.attr="disabled" type="submit">
                {{ $editModalTexts['submit'] }}
            </x-primary-button>
            <script type="text/javascript">
                window.addEventListener('setSelect2', event => {
                    $('#admin_roles').select2({multiple: true,}).val(event.detail).change();
                });
            </script>
        </x-slot>
    </form>

</x-form-modal>
<!-- /Edit Items Confirmation Modal -->
