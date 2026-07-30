<!-- Edit Items Confirmation Modal -->
<x-form-modal wire:model="showEditModal" type="edit" wire="update({{$setting['id'] ?? null}})">
    <x-slot name="title">
        {{ $editModalTexts['title'] }}
    </x-slot>
    <form wire:submit.prevent="update({{$setting['id'] ?? null}})">
        <x-slot name="content">
            <div class="form-group">
                <label for="name">{{__('pages/system/settings/settings.modal.edit.inputs.name')}}</label>
                <input type="text" class="form-control" disabled id="name" name="name" wire:model.defer="setting.name">
            </div>

            <div class="form-group">
                <label for="value">{{__('pages/system/settings/settings.modal.edit.inputs.value')}}</label>
                @isset($setting['key'])
                    @if(in_array(($setting['key'] ?? null), ['site.logo', 'payment.qr_image'], true))
                        @if(!empty($setting['value']))
                            <div class="mb-2">
                                @php
                                    $logoValue = $setting['value'];
                                    if (\Illuminate\Support\Str::startsWith($logoValue, ['http://', 'https://'])) {
                                        $logoSrc = $logoValue;
                                    } elseif (\Illuminate\Support\Str::startsWith($logoValue, 'uploads/')) {
                                        $logoSrc = '/image/' . $logoValue;
                                    } else {
                                        $logoSrc = '/' . ltrim($logoValue, '/');
                                    }
                                @endphp
                                <img src="{{ $logoSrc }}"
                                     alt="preview" class="rounded"
                                     style="width: 120px; height: 120px; object-fit: contain;">
                            </div>
                        @endif
                        <input type="file" accept="image/*"
                               class="form-control h-auto @error('logo_upload') is-invalid @enderror"
                               id="value"
                               wire:model="logo_upload">
                        @error('logo_upload')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <small class="form-text text-muted">{{__('pages/system/settings/settings.modal.edit.inputs.logo_hint')}}</small>
                    @elseif(($setting['value_type'] ?? null) === 'boolean')
                        <select class="form-control @error('setting.value') is-invalid @enderror"
                                wire:model.defer="setting.value"
                                id="value">
                            <option
                                value="1">{{__('pages/system/settings/settings.modal.edit.inputs.boolean.yes')}}</option>
                            <option
                                value="0">{{__('pages/system/settings/settings.modal.edit.inputs.boolean.no')}}</option>
                        </select>
                    @elseif(($setting['value_type'] ?? null) === 'string')
                        <input type="text" class="form-control @error('setting.value') is-invalid @enderror" id="value"
                               name="value"
                               wire:model.defer="setting.value">
                    @elseif(($setting['value_type'] ?? null) === 'longText')
                        <textarea type="text" class="form-control @error('setting.value') is-invalid @enderror"
                                  id="value"
                                  name="value"
                                  wire:model.defer="setting.value"></textarea>
                    @else
                        <input type="number" min="0" class="form-control @error('setting.value') is-invalid @enderror"
                               id="value"
                               name="value"
                               wire:model.defer="setting.value">
                    @endif
                    @error('setting.value')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                @endisset

            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeEditModal" wire:loading.attr="disabled">
                {{ $editModalTexts['cancel'] }}
            </x-secondary-button>

            <x-primary-button wire:loading.attr="disabled" type="submit">
                {{ $editModalTexts['submit'] }}
            </x-primary-button>
        </x-slot>
    </form>
</x-form-modal>
<!-- /Edit Items Confirmation Modal -->
