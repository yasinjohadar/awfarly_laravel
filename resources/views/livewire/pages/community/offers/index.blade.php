<div class="card">
    <div class="card-header">
        @if($offer_id)
            <h5 class="card-title mb-0">{{ __('pages/community/offers/index.content.title') }}</h5>
        @else
            <h5 class="card-title">{{__('pages/community/offers/index.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($offer_id)
            @livewire('community.offers.community-offers-show-component', ['offer_id' => $offer_id], key($offer_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/community/offers/index.content.tabs.all', ['count' => $all_offers_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('active')"
                                class="nav-link bg-transparent{{$page_type === 'active' ? ' active' : ''}}">
                            {{__('pages/community/offers/index.content.tabs.active', ['count' => $active_offers_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('unreviewed')"
                                class="nav-link bg-transparent{{$page_type === 'unreviewed' ? ' active' : ''}}">
                            {{__('pages/community/offers/index.content.tabs.unreviewed', ['count' => $unreviewed_offers_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('expired')"
                                class="nav-link bg-transparent{{$page_type === 'expired' ? ' active' : ''}}">
                            {{__('pages/community/offers/index.content.tabs.expired', ['count' => $expired_offers_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('deleted')"
                                class="nav-link bg-transparent{{$page_type === 'deleted' ? ' active' : ''}}">
                            {{__('pages/community/offers/index.content.tabs.deleted', ['count' => $deleted_offers_count])}}
                        </button>
                    </li>
                </ul>
                @livewire('community.offers.community-offers-inquiry-component', ['activeNumberFilters' => $activeNumberFilters ?? []])
            </div>
        @endif
    </div>
</div>
