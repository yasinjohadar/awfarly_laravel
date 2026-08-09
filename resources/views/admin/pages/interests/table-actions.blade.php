<div class="d-flex justify-content-around">
    <button @cannot('interests.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})"
            class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>
    <button title="{{__('datatable.delete')}}" @cannot('interests.delete') disabled @endcannot
            wire:click="showDeleteModal({{ $id }})" class="btn btn-danger mx-1">
        <i class="icon-trash"></i>
    </button>
    @isset($parent)
        <button title="{{__('pages/interests/index.content.sub_interests_count')}}" @cannot('interests.inquiry') disabled @endcannot  wire:click="$emitUp('setInterestId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-folder-open"></i>
        </button>
    @endisset
</div>
