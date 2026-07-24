@props(['id' => null, 'maxWidth' => null, 'type'=>null, 'wire'=>null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <form wire:submit.prevent="{{$wire}}">
        <div class="modal-content">
            <div class="modal-body">
                <div class="d-flex justify-content-start">
                    <div class="mr-3">
                        @if($type === 'delete')
                            <div class="bg-warning p-2 rounded-circle">
                                <i class="icon-warning22 d-block p-1"></i>
                            </div>
                        @elseif($type === 'edit')
                            <div class="bg-primary p-2 rounded-circle">
                                <i class="icon-pencil5 d-block p-1"></i>
                            </div>
                        @elseif($type === 'add')
                            <div class="bg-primary p-2 rounded-circle">
                                <i class="icon-add-to-list d-block p-1"></i>
                            </div>
                        @endif
                    </div>
                    <div class="w-100">
                        <h5 class="font-weight-bold">{{ $title }}</h5>
                        {{ $content }}
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                {{ $footer }}
            </div>
        </div>
    </form>
</x-modal>
