<div x-data>
    <div class="input-group input-group-sm mb-2">
        <input x-ref="inputNumberStart" type="number" wire:input.debounce.500ms="doNumberFilterStart('{{ $index }}', $event.target.value)" class="form-control rounded pr-4" placeholder="{{ __('datatable.min') }}">

        <div class="empty-filters pr-1 align-items-center h-100">
            <button x-on:click="$refs.inputNumberStart.value = ''" wire:click="doNumberFilterStart('{{ $index }}', '')" class="border-0" type="button">
                <x-icons.x-circle class="ml-1" width="20" height="20" />
            </button>
        </div>
    </div>

    <div class="input-group input-group-sm mb-2">
        <input x-ref="inputNumberEnd" type="number" wire:input.debounce.500ms="doNumberFilterEnd('{{ $index }}', $event.target.value)" class="form-control rounded pr-4" placeholder="{{__('datatable.max')}}">

        <div class="empty-filters pr-1 align-items-center h-100">
            <button x-on:click="$refs.inputNumberEnd.value = ''" wire:click="doNumberFilterEnd('{{ $index }}', '')" class="border-0" type="button">
                <x-icons.x-circle class="ml-1" width="20" height="20" />
            </button>
        </div>
    </div>
</div>
