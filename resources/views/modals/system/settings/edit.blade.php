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
                @isset($setting['value_type'])
                    @if ($setting['value_type'] === 'boolean')
                        <select class="form-control @error('setting.value') is-invalid @enderror"
                                wire:model.defer="setting.value"
                                id="value">
                            <option
                                value="1">{{__('pages/system/settings/settings.modal.edit.inputs.boolean.yes')}}</option>
                            <option
                                value="0">{{__('pages/system/settings/settings.modal.edit.inputs.boolean.no')}}</option>
                        </select>
                    @elseif($setting['value_type'] === 'string')
                        <input type="text" class="form-control @error('setting.value') is-invalid @enderror" id="value"
                               name="value"
                               wire:model.defer="setting.value">
                    @elseif($setting['value_type'] === 'longText')
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

