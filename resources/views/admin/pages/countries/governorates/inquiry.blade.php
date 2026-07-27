<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
        @if($governorate)
            <h5 class="card-title mb-0">{!! __('pages/countries/cities/index.content.title', ['name' => $governorate->{(App::getLocale() === 'ar' ? 'name_ar' : 'name_en')}]) !!}</h5>
        @else
            <h5 class="card-title mb-0">{{__('pages/countries/governorates/index.content.title_all')}}</h5>
            @can('governorates.add')
                <a href="{{route('admin.governorates.create')}}" class="btn btn-primary">
                    {{__('pages/countries/governorates/index.content.add')}}
                </a>
            @endcan
        @endif
    </div>
    <div class="card-body">
        @if($governorate)
            <div class="form-group">
                <button class="btn btn-secondary"
                        @if(isset($order) && $order == true) wire:click="$emitSelf('setGovernorateId', {{$governorate->id}}, false)"
                        @else wire:click="$emitSelf('setGovernorateId', null)" @endisset>{{__('pages/countries/inquiry.content.back')}}</button>
                @empty($order)
                    <button class="btn btn-primary"
                            wire:click="$emitTo('countries.cities.cities-inquiry-component', 'showAddModal')">{{__('pages/countries/inquiry.content.add')}}</button>
                    @if($governorate->cities()->count() > 0)
                        <button class="btn btn-secondary"
                                wire:click="$emitSelf('setGovernorateId', {{$governorate->id}}, true)">{{__('pages/countries/inquiry.content.sort')}}</button>
                    @endif
                @endempty
            </div>
            @if($order)
                @livewire('countries.cities.cities-sort-component', ['governorate_id' => $governorate->id])
            @else
                @livewire('countries.cities.cities-inquiry-component', ['governorate_id' => $governorate->id])
            @endif
        @else
            @livewire('countries.governorates.governorates-inquiry-component')
        @endif
    </div>
</div>
