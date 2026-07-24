<div x-data>
    <div class="input-group input-group-sm mb-2">
        <input x-ref="inputTimeStart" type="time" class="form-control rounded" wire:change="doTimeFilterStart('{{ $index }}', $event.target.value)">

        <div class="input-group-append">
            <button x-on:click="$refs.inputTimeStart.value = ''" wire:click="doTimeFilterStart('{{ $index }}', '')" class="btn" type="button">
                @include('livewire.datatables.icons.x-circle')
            </button>
        </div>
    </div>

    <div class="input-group input-group-sm mb-2">
        <input x-ref="inputTimeEnd" type="time" class="form-control rounded" wire:change="doTimeFilterEnd('{{ $index }}', $event.target.value)">

        <div class="input-group-append">
            <button x-on:click="$refs.inputTimeEnd.value = ''" wire:click="doTimeFilterEnd('{{ $index }}', '')" class="btn" type="button">
                @include('livewire.datatables.icons.x-circle')
            </button>
        </div>
    </div>
</div>
