<div class="d-flex justify-content-around">
    <button @cannot('modal.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})"
            class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>
    {{--@isset($parent)
        <button @cannot('categories.inquiry') disabled @endcannot  wire:click="$emitUp('setCategoryId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-folder-open"></i>
        </button>
    @endisset--}}
</div>
