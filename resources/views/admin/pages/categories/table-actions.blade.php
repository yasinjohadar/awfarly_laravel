<div class="d-flex justify-content-around">
    <button @cannot('categories.edit') disabled @endcannot  wire:click="showEditModal({{ $id }})"
            class="btn btn-secondary">
        <i class="icon-pencil7"></i>
    </button>
    <button title="{{__('datatable.delete')}}" @cannot('categories.delete') disabled @endcannot
            wire:click="showDeleteModal({{ $id }})" class="btn btn-danger mx-1">
        <i class="icon-trash"></i>
    </button>
    @can('categories.inquiry')
        <a title="{{__('pages/categories/show.content.sub_categories.view')}}"
           href="{{ route('admin.categories.show', $id) }}"
           class="btn btn-info mr-1">
            <i class="icon-info22"></i>
        </a>
    @endcan
    @isset($parent)
        <button title="{{__('pages/categories/index.content.sub_categories_count')}}" @cannot('categories.inquiry') disabled @endcannot  wire:click="$emitUp('setCategoryId', {{ $id }})"
                class="btn btn-secondary">
            <i class="icon-folder-open"></i>
        </button>
    @endisset
</div>
