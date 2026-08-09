<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
        @if($interest)
            <h5 class="card-title mb-0">{!! __('pages/interests/index.content.title', ['name' => $interest->{(App::getLocale() === 'ar' ? 'name_ar' : 'name_en')}]) !!}</h5>
        @else
            <h5 class="card-title mb-0">{{__('pages/interests/inquiry.content.title')}}</h5>
            @can('interests.add')
                <a href="{{route('admin.interests.create')}}" class="btn btn-primary">
                    {{__('pages/interests/inquiry.content.add')}}
                </a>
            @endcan
        @endif
    </div>

    <div class="card-body">
        <div class="form-group">
            @if($interest)
                <button class="btn btn-secondary"
                        @if(isset($order) && $order == true) wire:click="$emitSelf('setInterestId', {{$interest->id}}, false)"
                        @else
                        wire:click="$emitSelf('setInterestId', null)" @endisset>{{__('pages/interests/index.content.back')}}</button>
                @empty($order)
                    @can('interests.add')
                        <button class="btn btn-primary"
                                wire:click="$emitTo('interests.interest-inquiry-component', 'showAddModal')">{{__('pages/interests/index.content.add')}}</button>
                    @endcan
                    @if($interest->childInterests()->count() > 0)
                        <button class="btn btn-secondary"
                                wire:click="$emitSelf('setInterestId', {{$interest->id}}, true)">{{__('pages/interests/index.content.sort')}}</button>
                    @endif
                @endempty
            @else
                @if($order)
                    <button class="btn btn-secondary"
                            wire:click="$emitSelf('setInterestId', null)">{{__('pages/interests/index.content.back')}}</button>
                @endif
                @empty($order)
                    <button class="btn btn-secondary"
                            wire:click="$emitSelf('setInterestId', null, true)">{{__('pages/interests/index.content.sort')}}</button>
                @endempty
            @endif
        </div>
        @if($interest)
            @if($order)
                @livewire('interests.interest-sort-component', ['interest_id' => $interest->id])
            @else
                @livewire('interests.interest-inquiry-component', ['interest_id' => $interest->id])
            @endif
        @else
            @if($order)
                @livewire('interests.interest-sort-component')
            @else
                @livewire('interests.interests-inquiry-component')
            @endempty
        @endif
    </div>
</div>


@push('scripts')
    <script type="text/javascript">
        //add event listener to refresh file input
        window.addEventListener('clearAddFileInput', () => {
            $('#interest_image').val(null);
        });

        //add event listener to refresh file input
        window.addEventListener('clearFileInput', () => {
            $('#new_image').val(null);
        });
    </script>
@endpush
