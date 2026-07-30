<x-form-modal wire:model="showAssignPackageModal" type="edit" wire="assignPackage">
    <x-slot name="title">
        {{ __('pages/advertisers/index.modal.assign_package.title') }}
    </x-slot>
    <x-slot name="content">
        <div class="mb-3">
            <div class="text-muted small mb-1">{{ __('pages/advertisers/index.modal.assign_package.advertiser') }}</div>
            <div class="font-weight-bold">{{ $assign_advertiser_name }}</div>
        </div>

        <div class="alert alert-info mb-3">
            {{ __('pages/advertisers/index.modal.assign_package.notes') }}
        </div>

        <div class="form-group mb-0" wire:ignore
             x-on:change-assign-package-id.window="assign_package_id = $event.detail;
             $('#assign_package_id').select2({
                    placeholder: '{{ __('pages/advertisers/index.modal.assign_package.placeholders.package') }}',
                    allowClear: true,
                    cache: true,
                    dropdownParent: $('#assign_package_id').closest('.modal')
                }).val(assign_package_id).change()"
             x-data="{assign_package_id: @entangle('assign_package_id').defer}"
             x-init="$nextTick(() => {
                 select2 = $('#assign_package_id').select2({
                    placeholder: '{{ __('pages/advertisers/index.modal.assign_package.placeholders.package') }}',
                    cache: true,
                    allowClear: true,
                    dropdownParent: $('#assign_package_id').closest('.modal')
                }).val(assign_package_id).change();
                select2.on('change', (event) => {
                    assign_package_id = event.target.value;
                });
            })">
            <label for="assign_package_id">{{ __('pages/advertisers/index.modal.assign_package.package') }}</label>
            <select x-model="assign_package_id" x-cloak
                    data-placeholder="{{ __('pages/advertisers/index.modal.assign_package.placeholders.package') }}"
                    id="assign_package_id"
                    class="form-control @error('assign_package_id') is-invalid @enderror">
                <option></option>
                @foreach ($packages as $package)
                    <option value="{{ $package['id'] }}">{{ $package['name'] }}</option>
                @endforeach
            </select>
        </div>
        @error('assign_package_id')
        <div class="invalid-feedback d-block mt-1" role="alert">
            <strong>{{ $message }}</strong>
        </div>
        @enderror
    </x-slot>
    <x-slot name="footer">
        <x-secondary-button wire:click="closeAssignPackageModal" wire:loading.attr="disabled">
            {{ __('pages/advertisers/index.modal.assign_package.cancel') }}
        </x-secondary-button>
        <x-primary-button type="submit" wire:loading.attr="disabled">
            {{ __('pages/advertisers/index.modal.assign_package.submit') }}
        </x-primary-button>
    </x-slot>
</x-form-modal>

<x-confirmation-modal wire:model="showStatusModal" type="delete">
    <x-slot name="title">
        {{ $statusModalTexts['title'] ?? '' }}
    </x-slot>
    <x-slot name="content">
        {{ $statusModalTexts['content'] ?? '' }}
    </x-slot>
    <x-slot name="footer">
        <x-secondary-button wire:click="closeStatusModal" wire:loading.attr="disabled">
            {{ $statusModalTexts['cancel'] ?? __('pages/advertisers/index.modal.assign_package.cancel') }}
        </x-secondary-button>
        <x-danger-button wire:loading.attr="disabled" wire:click="confirmStatusChange">
            {{ $statusModalTexts['submit'] ?? '' }}
        </x-danger-button>
    </x-slot>
</x-confirmation-modal>
