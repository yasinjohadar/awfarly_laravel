<div class="card">
    <div class="card-header">
        @if($modal)
            <h5 class="card-title">{!! __('pages/modals/index.content.title', ['name' => $modal->{(App::getLocale() === 'ar' ? 'title_ar' : 'title_en')}]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/modals/inquiry.content.title')}}</h5>
        @endif
    </div>

    <div class="card-body">
        <div class="form-group">
            @if($modal)
                <button class="btn btn-secondary"
                        @if(isset($order) && $order == true) wire:click="$emitSelf('setModalId', {{$modal->id}}, false)"
                        @else
                        wire:click="$emitSelf('setModalId', null)" @endisset>{{__('pages/modals/index.content.back')}}</button>
                @empty($order)
                    {{--<button class="btn btn-primary"
                            wire:click="$emitTo('modals.modal-inquiry-component', 'showAddModal')">{{__('pages/modals/index.content.add')}}</button>--}}
                    @if($modal->childCategories()->count() > 0)
                        {{-- <button class="btn btn-secondary"
                                wire:click="$emitSelf('setModalId', {{$modal->id}}, true)">{{__('pages/modals/index.content.sort')}}</button> --}}
                    @endif
                @endempty
            @else
                @if($order)
                    <button class="btn btn-secondary"
                            wire:click="$emitSelf('setModalId', null)">{{__('pages/modals/index.content.back')}}</button>
                @endif
                @empty($order)
                    {{-- <button class="btn btn-secondary"
                            wire:click="$emitSelf('setModalId', null, true)">{{__('pages/modals/index.content.sort')}}</button> --}}
                @endempty
            @endif
        </div>
        @if($modal)
            @if($order)
                {{-- @livewire('modals.modal-sort-component', ['modal_id' => $modal->id]) --}}
            @else
                @livewire('modals.modal-inquiry-component', ['modal_id' => $modal->id])
            @endif
        @else
            @if($order)
                {{-- @livewire('modals.modal-sort-component') --}}
            @else
                @livewire('modals.modals-inquiry-component')
            @endempty
        @endif
    </div>
</div>


@push('scripts')
    <script type="text/javascript">
        //add event listener to refresh file input
        window.addEventListener('clearAddFileInput', () => {
            $('#modal_image').val(null);
        });

        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#new_image').val(null);
        });
    </script>
@endpush
