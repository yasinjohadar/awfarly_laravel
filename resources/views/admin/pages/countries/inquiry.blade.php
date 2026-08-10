<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
        @if($governorate)
            <h5 class="card-title mb-0">{!! __('pages/countries/cities/index.content.title', ['name' => $governorate->{(App::getLocale() === 'ar' ? 'name_ar' : 'name_en')}]) !!}</h5>
        @elseif($country)
            <h5 class="card-title mb-0">{!! __('pages/countries/governorates/index.content.title', ['name' => $country->{(App::getLocale() === 'ar' ? 'name_ar' : 'name_en')}]) !!}</h5>
        @else
            <h5 class="card-title mb-0">{{__('pages/countries/index.content.title')}}</h5>
            @can('countries.add')
                <a href="{{route('admin.countries.create')}}" class="btn btn-primary">
                    {{__('pages/countries/index.content.add')}}
                </a>
            @endcan
        @endif
    </div>
    <div class="card-body">
        <div class="form-group">
            @if($governorate)
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
            @elseif($country)
                <button class="btn btn-secondary"
                        @if(isset($order) && $order == true) wire:click="$emitSelf('setCountryId', {{$country->id}}, false)"
                        @else
                        wire:click="$emitSelf('setCountryId', null)" @endisset>{{__('pages/countries/inquiry.content.back')}}</button>
                @empty($order)
                    <button class="btn btn-primary"
                            wire:click="$emitTo('countries.governorates.governorates-inquiry-component', 'showAddModal')">{{__('pages/countries/inquiry.content.add')}}</button>
                    @if($country->governorates()->count() > 0)
                        <button class="btn btn-secondary"
                                wire:click="$emitSelf('setCountryId', {{$country->id}}, true)">{{__('pages/countries/inquiry.content.sort')}}</button>
                    @endif
                @endempty
            @else
                @if($order)
                    <button class="btn btn-secondary"
                            wire:click="$emitSelf('setCountryId', null)">{{__('pages/countries/inquiry.content.back')}}</button>
                @endif
                @empty($order)
                    <button class="btn btn-secondary"
                            wire:click="$emitSelf('setCountryId', null, true)">{{__('pages/countries/inquiry.content.sort')}}</button>
                @endempty
            @endif
        </div>
        @if($governorate)
            @if($order)
                @livewire('countries.cities.cities-sort-component', ['governorate_id' => $governorate->id])
            @else
                @livewire('countries.cities.cities-inquiry-component', ['governorate_id' => $governorate->id])
            @endif
        @elseif($country)
            @if($order)
                @livewire('countries.governorates.governorates-sort-component', ['country_id' => $country->id])
            @else
                @livewire('countries.governorates.governorates-inquiry-component', ['country_id' => $country->id])
            @endif
        @else
            @if($order)
                @livewire('countries.countries-sort-component')
            @else
                @livewire('countries.countries-inquiry-component')
            @endif
        @endif
    </div>
</div>
