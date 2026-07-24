<div x-data class="input-group input-group-sm mb-2">
    <select x-ref="select" wire:input="doSelectFilter('{{ $index }}', $event.target.value)"
            x-on:input="$refs.select.value=''" name="{{ $name }}" class="custom-select m-1 rounded">
        <option value="">{{ __('datatable.choose') }}</option>
        @foreach($options as $value => $label)
            @if(is_object($label))
                <option value="{{ $label->id }}">{{ $label->name }}</option>
            @elseif(is_array($label))
                <option value="{{ $label['id'] }}">{{ $label['name'] }}</option>
            @elseif(is_numeric($value))
                <option value="{{ $label }}">{{ $label }}</option>
            @else
                <option value="{{ $value }}">{{ $label }}</option>
            @endif
        @endforeach
    </select>
</div>
<div class="flex flex-wrap mx-1">
    @foreach($this->activeSelectFilters[$index] ?? [] as $key => $value)
        <button wire:click="removeSelectFilter('{{ $index }}', '{{ $key }}')"
                class="p-0 border-0 badge badge-pill badge-primary flex align-items-center text-uppercase">
            <span class="ml-2 mr-1 line-height-normal">{{ $this->getDisplayValue($index, $value) }}</span>
            <x-icons.x-circle class="ml-1" width="20" height="20"/>
        </button>
    @endforeach
</div>
