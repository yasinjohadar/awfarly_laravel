<div x-data="{ show: false }">
    <div class="position-relative">
        <button x-on:click="show = !show" class="btn btn-outline-primary">
            @include('livewire.datatables.icons.table', ['text' => __('datatable.show') . ' / ' . __('datatable.hide')])
        </button>

        <div class="card card-primary rounded mt-1" style="position:absolute;opacity:0.85;z-index:5;" x-show="show" x-on:click.away="show = false" x-cloak>
            <div class="card-header">
                {{ __('datatable.table_col') }}
            </div>
            <div class="card-body">
                @foreach($this->columns as $index => $column)
                    @if ($column['type'] !== 'checkbox')
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="column_{{ $index }}" @if(!$column['hidden']) checked @endif>
                            <small wire:click="toggle({{$index}})" class="custom-control-label" for="column_{{ $index }}">{{ $column['label'] }}</small>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

    </div>
</div>
