<div class="card">
    <div class="card-header">
        @if($rating_id)
            <h5 class="card-title">{!! __('pages/community/offers/ratings/inquiry.content.title', ['id' => $rating_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/community/offers/ratings/index.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($rating_id)
            @livewire('community.offers.ratings.community-offers-ratings-show-component', ['rating_id' => $rating_id], key($rating_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/community/offers/ratings/index.content.tabs.all', ['count' => $all_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('approved')"
                                class="nav-link bg-transparent{{$page_type === 'approved' ? ' active' : ''}}">
                            {{__('pages/community/offers/ratings/index.content.tabs.approved', ['count' => $approved_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('pending')"
                                class="nav-link bg-transparent{{$page_type === 'pending' ? ' active' : ''}}">
                            {{__('pages/community/offers/ratings/index.content.tabs.pending', ['count' => $pending_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('unapproved')"
                                class="nav-link bg-transparent{{$page_type === 'unapproved' ? ' active' : ''}}">
                            {{__('pages/community/offers/ratings/index.content.tabs.unapproved', ['count' => $unapproved_count])}}
                        </button>
                    </li>
                </ul>
                @livewire('community.offers.ratings.community-offers-ratings-inquiry-component')
            </div>
        @endif
    </div>
</div>

