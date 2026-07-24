<div x-data class="input-group input-group-sm mb-2">
    <input
        x-ref="input"
        type="text"
        class="m-1 text-sm form-control rounded"
        wire:change="doTextFilter('{{ $index }}', $event.target.value)"
        placeholder="{{ __('datatable.type_find') }}"
        x-on:change="$refs.input.value = ''"
    />
</div>
<div class="flex flex-wrap mx-1">
    @foreach($this->activeTextFilters[$index] ?? [] as $key => $value)
        <button wire:click="removeTextFilter('{{ $index }}', '{{ $key }}')" class="p-0 border-0 badge badge-pill badge-primary flex align-items-center text-uppercase">
            <span class="ml-2 mr-1 line-height-normal">{{ $this->getDisplayValue($index, $value) }}</span>
            <x-icons.x-circle class="ml-1" width="20" height="20" />
        </button>
    @endforeach
</div>
