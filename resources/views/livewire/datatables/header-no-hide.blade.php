@unless($column['hidden'])
    <div class="table-cell p-0" @isset($column['width']) style="width:{{ $column['width'] }}" @endisset>
        @if($column['unsortable'])
            <span
                class="flex unsortable @if($column['align'] === 'right') justify-content-end @elseif($column['align'] === 'center') justify-content-center @endif">
                <span class="d-inline ">{{ str_replace('_', ' ', $column['label']) }}</span>
            </span>
        @else

            <button wire:click.prefetch="sort('{{ $index }}')"
                    class="flex @if($column['align'] === 'right') justify-content-end @elseif($column['align'] === 'center') justify-content-center @endif">
                <span class="d-inline ">{{ str_replace('_', ' ', $column['label']) }}</span>
                <span class="d-inline small">
            @if($sort === $index)
                        @if($direction)
                            @include('livewire.datatables.icons.chevron-up')
                        @else
                            @include('livewire.datatables.icons.chevron-down')
                        @endif
                    @endif
        </span>
            </button>

        @endif
    </div>
@endif
