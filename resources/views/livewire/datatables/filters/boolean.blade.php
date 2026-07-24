<div x-data class="input-group input-group-sm mb-2">
    <select x-ref="boolean" name="{{ $name }}" class="custom-select rounded m-1"
            wire:input="doBooleanFilter('{{ $index }}', $event.target.value)" x-on:input="$refs.boolean.value=''">
        <option value="">{{ __('datatable.choose') }}</option>
        <option value="0">{{ __('datatable.no') }}</option>
        <option value="1">{{ __('datatable.yes') }}</option>
    </select>
</div>
<div class="flex flex-wrap mx-1">
    @isset($this->activeBooleanFilters[$index])
        @if($this->activeBooleanFilters[$index] == 1)
            <button wire:click="removeBooleanFilter('{{ $index }}')"
                    class="p-0 border-0 badge badge-pill badge-primary flex align-items-center text-uppercase">
                <span class="ml-2 mr-1 line-height-normal">{{ __('datatable.yes') }}</span>
                <x-icons.x-circle class="ml-1" width="20" height="20"/>
            </button>
        @elseif(strlen($this->activeBooleanFilters[$index]) > 0)
            <button wire:click="removeBooleanFilter('{{ $index }}')"
                    class="p-0 border-0 badge badge-pill badge-primary flex align-items-center text-uppercase">
                <span class="ml-2 mr-1 line-height-normal">{{ __('datatable.no') }}</span>
                <x-icons.x-circle class="ml-1" width="20" height="20"/>
            </button>
        @endif
    @endisset
</div>
