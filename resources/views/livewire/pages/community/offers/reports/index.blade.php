<div class="card">
    <div class="card-header">
        @if($offer_id)
            <h5 class="card-title">{!! __('pages/community/offers/reports/show.content.title', ['id' => $offer_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/community/offers/reports/reports.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($offer_id)
            @livewire('community.offers.reports.community-reported-offer-inquiry-component', ['offer_id' => $offer_id, 'active' => $page_type], key($offer_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/community/offers/reports/index.content.tabs.all', ['count' => $all_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('solved')"
                                class="nav-link bg-transparent{{$page_type === 'solved' ? ' active' : ''}}">
                            {{__('pages/community/offers/reports/index.content.tabs.solved', ['count' => $solved_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('pending')"
                                class="nav-link bg-transparent{{$page_type === 'pending' ? ' active' : ''}}">
                            {{__('pages/community/offers/reports/index.content.tabs.pending', ['count' => $pending_reports_count])}}
                        </button>
                    </li>
                </ul>
                @livewire('community.offers.reports.community-reported-offers-inquiry-component')
            </div>
        @endif
    </div>
</div>

@push('scripts')
@endpush
