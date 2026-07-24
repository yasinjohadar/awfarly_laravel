<div class="card">
    <div class="card-header">
        @if($advertisement_id)
            <h5 class="card-title">{!! __('pages/advertisements/slider/inquiry.content.title', ['id' => $advertisement_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/advertisements/slider/index.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($advertisement_id)
            @livewire('advertisements.slider.slider-advertisements-show-component', ['advertisement_id' => $advertisement_id], key($advertisement_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/advertisements/slider/index.content.tabs.all', ['count' => $all_advertisements_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('active')"
                                class="nav-link bg-transparent{{$page_type === 'active' ? ' active' : ''}}">
                            {{__('pages/advertisements/slider/index.content.tabs.active', ['count' => $active_advertisements_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('expired')"
                                class="nav-link bg-transparent{{$page_type === 'expired' ? ' active' : ''}}">
                            {{__('pages/advertisements/slider/index.content.tabs.expired', ['count' => $expired_advertisements_count])}}
                        </button>
                    </li>
                </ul>
                @livewire('advertisements.slider.slider-advertisements-inquiry-component')
            </div>
        @endif
    </div>
</div>

