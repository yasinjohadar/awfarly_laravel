<div>
    @if($beforeTableSlot)
        @include($beforeTableSlot)
    @endif
    <div class="mb-1">
        <div class="row input-group mb-2 justify-content-between justify-items-center w-100">
            <div class="col-md-4 my-md-0 my-1">
                @if($this->searchableColumns()->count())
                    <div class="rounded">
                        <div class="position-relative">
                            <input wire:model.debounce.500ms="search" class="form-control rounded mr-1"
                                   placeholder="{{ __('datatable.search') }}"/>

                            <div class="empty-filters pr-2 align-items-center h-100">
                                <button wire:click="$set('search', null)" class="border-0">
                                    <x-icons.x-circle class="h-5 w-5"/>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-md-8">
                <div class="row justify-content-end">
                    <div class="col-auto my-md-0 my-1">
                        <div class="align-items-center">
                            <div wire:loading.class="spinner-border text-secondary mr-2"></div>
                        </div>
                    </div>
                    <div class="col-auto my-md-0 my-1">
                        <select name="perPage" id="perPage"
                                class="form-control"
                                wire:model="perPage">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option
                                value="99999999">{{__('datatable.all')}}</option>
                        </select>
                    </div>
                    <div class="col-auto my-md-0 my-1">
                        <button wire:click="$refresh" class="btn btn-outline-info">
                            {{__('datatable.refresh')}}
                        </button>
                    </div>
                    @if($exportable)
                        <div class="col-auto my-md-0 my-1">
                            <div id="export-excel">
                                <div
                                    x-data="{ init() { window.livewire.on('startDownload', link => window.open(link,'_blank')) } }"
                                    x-init="init">
                                    <button wire:click="export" class="btn btn-outline-success">
                                        @include('livewire.datatables.icons.excel', ['text' => __('datatable.export')])
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($hideable === 'select')
                        <div class="col-auto my-md-0 my-1">
                            <div id="hideable-select">
                                @include('livewire.datatables.hide-column-multiselect')
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @switch($hideable)
        @case('buttons')
        <div class="mb-1">
            <div class="p-2">
                @foreach($this->columns as $index => $column)
                    @if ($column['type'] !== 'checkbox')
                        <span class="btn btn-outline-primary m-2{{($column['hidden'] !== true ? ' active' : '')}}"
                              wire:click.prefech="toggle('{{ $index }}')">
                                {{ str_replace('_', ' ', $column['label']) }}
                            @if($column['hidden'])
                                @include('livewire.datatables.icons.eye-slash')
                            @else
                                @include('livewire.datatables.icons.eye')
                            @endif
                            </span>
                    @endif
                @endforeach
            </div>
        </div>
        @break
    @endswitch
    <div class="rounded-lg shadow-lg bg-white w-100 overflow-auto">
        <div class="rounded-lg @unless($this->hidePagination) rounded-0 @endif">
            <div class="table align-middle w-100">
                @unless($this->hideHeader)
                    <div class="table-row header">
                        @foreach($this->columns as $index => $column)
                            @if($hideable === 'inline')
                                @include('livewire.datatables.header-inline-hide', ['column' => $column, 'sort' => $sort])
                            @elseif($column['type'] === 'checkbox')
                                <div
                                    class="table-cell">
                                    <div
                                        class="px-3 py-1 rounded{{count($selected) ? ' bg-secondary' : ' bg-dark'}} text-white text-center">
                                        {{ count($selected) }}
                                    </div>
                                </div>
                            @else
                                @include('livewire.datatables.header-no-hide', ['column' => $column, 'sort' => $sort, 'name' => $column['name']])
                            @endif
                        @endforeach
                    </div>

                    <div class="table-row filters">
                        @foreach($this->columns as $index => $column)
                            @if($column['hidden'])
                                @if($hideable === 'inline')
                                    <div class="table-cell"></div>
                                @endif
                            @elseif($column['type'] === 'checkbox')
                                <div
                                    class="overflow-hidden text-left flex h-100 flex-col align-items-center">
                                    <div
                                        class="uppercase text-nowrap">{{__('datatable.select_all')}}</div>
                                    <div class="custom-control custom-switch">
                                        <input id="selectAll" type="checkbox" wire:click="toggleSelectAll"
                                               @if(count($selected) === $this->results->total()) checked
                                               @endif class="custom-control-input"/>
                                        <label class="custom-control-label" for="selectAll"></label>
                                    </div>
                                </div>
                            @else
                                <div class="table-cell overflow-hidden align-top">
                                    @isset($column['filterable'])
                                        @if( is_iterable($column['filterable']) )
                                            <div wire:key="{{ $index }}">
                                                @include('livewire.datatables.filters.select', ['index' => $index, 'name' => $column['label'], 'options' => $column['filterable']])
                                            </div>
                                        @else
                                            <div wire:key="{{ $index }}">
                                                @include('livewire.datatables.filters.' . ($column['filterView'] ?? $column['type']), ['index' => $index, 'name' => $column['label']])
                                            </div>
                                        @endif
                                    @endisset
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
                @foreach($this->results as $index => $result)
                    @php($isPendingRow = isset($result->status) && $result->status === 'pending')
                    <div
                        @if($isPendingRow) style="background-color: #fff3e0; border-inline-start: 4px solid #fb8c00;" @endif
                        class="table-row results p-1{{ isset($result->checkbox_attribute) && in_array($result->checkbox_attribute, $selected) ? ' bg-info' : ($isPendingRow ? '' : ($loop->even ? ' striped' : ' stripe')) }}">
                        @foreach($this->columns as $index => $column)
                            @if($column['hidden'])
                                @if($hideable === 'inline')
                                    <div class="table-cell w-50 overflow-hidden align-top"></div>
                                @endif
                            @elseif($column['type'] === 'checkbox')
                                <div class="overflow-hidden text-left flex h-100 flex-col align-items-center">
                                    @include('livewire.datatables.checkbox', ['value' => $result->checkbox_attribute])
                                </div>
                            @else
                                <div
                                    class="px-5 py-2 text-nowrap small align-middle text-gray-900 table-cell{{($column['align'] === 'right') ? ' text-right' : (($column['align'] === 'center') ? ' text-center' : ' text-left')}}">
                                    {!! $result->{$column['name']} !!}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
            @if(count($this->results) == 0)
                <p class="p-3 w-100 d-block">
                    {{__('datatable.no_data_table')}}
                </p>
            @endif
        </div>
        @unless($this->hidePagination)
            <div class="rounded-lg rounded-t-none max-w-screen rounded-lg border-b border-gray-200 bg-white">
                <div class="p-2 d-sm-flex justify-content-between">
                    {{-- check if there is any data --}}
                    @if($this->results)
                        <div class="my-2 my-sm-0 flex align-items-center">
                            {{ $this->results->onEachSide(0)->links('livewire.datatables.paginators.bootstrap') }}
                        </div>

                        <div class="my-2 my-sm-0 flex justify-content-end">
                            {{__('datatable.pagination_text', [
                                'start'=>$this->results->firstItem(),
                                'end'=>$this->results->lastItem(),
                                'total'=>$this->results->total()
                            ])}}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    @if($afterTableSlot)
        <div class="mt-5">
            @include($afterTableSlot)
        </div>
    @endif
    @isset($afterTableSlot2)
        @if($afterTableSlot2)
            <div class="mt-5">
                @include($afterTableSlot2)
            </div>
        @endif
    @endisset
    @isset($afterTableSlot3)
        @if($afterTableSlot3)
            <div class="mt-5">
                @include($afterTableSlot3)
            </div>
        @endif
    @endisset
</div>
