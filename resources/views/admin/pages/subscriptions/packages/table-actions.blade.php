<div class="d-flex justify-content-around">
    <div class="mx-2">
        <button @cannot('packages.inquiry') disabled @endcannot  wire:click="$emitUp('setPackageId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-eye"></i>
        </button>
    </div>
    <div class="mx-2">
        @can('packages.edit')
            <a title="Edit" href="{{ route('admin.subscriptions.packages.edit', $id) }}"
               class="btn btn-secondary mx-1">
                <i class="icon-pencil7"></i>
            </a>
        @else
            <button title="Edit" disabled class="btn btn-secondary mx-1">
                <i class="icon-pencil7"></i>
            </button>
        @endcan
    </div>
    <div class="mx-2">
        <button title="{{__('datatable.delete')}}" @cannot('packages.delete') disabled
                @endcannot wire:click="showDeleteModal({{ $id }})"
                class="btn btn-danger mx-1">
            <i class="icon-trash"></i>
        </button>
    </div>
</div>
