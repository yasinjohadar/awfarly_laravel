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
            <div class="offers-stats">
                <style>
                    .offers-stats__grid {
                        display: grid;
                        grid-template-columns: repeat(6, minmax(0, 1fr));
                        gap: 1rem;
                        margin-bottom: 1.5rem;
                    }

                    @media (max-width: 1199px) {
                        .offers-stats__grid {
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                        }
                    }

                    @media (max-width: 767px) {
                        .offers-stats__grid {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }
                    }

                    .offers-stats__card {
                        position: relative;
                        overflow: hidden;
                        border-radius: 1rem;
                        padding: 1rem 1.1rem;
                        color: #fff;
                        box-shadow: 0 8px 22px rgba(15, 23, 42, .1);
                    }

                    .offers-stats__card::after {
                        content: "";
                        position: absolute;
                        inset: auto -1.5rem -2rem auto;
                        width: 6.5rem;
                        height: 6.5rem;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, .12);
                    }

                    .offers-stats__card--total {
                        background: linear-gradient(135deg, #42a5f5 0%, #2e86d6 60%, #1d599f 100%);
                    }

                    .offers-stats__card--active {
                        background: linear-gradient(135deg, #66bb6a 0%, #2e7d32 60%, #1b5e20 100%);
                    }

                    .offers-stats__card--pending {
                        background: linear-gradient(135deg, #ffa726 0%, #fb8c00 60%, #ef6c00 100%);
                    }

                    .offers-stats__card--expired {
                        background: linear-gradient(135deg, #7e57c2 0%, #5e35b1 60%, #4527a0 100%);
                    }

                    .offers-stats__card--unapproved {
                        background: linear-gradient(135deg, #ef5350 0%, #c62828 60%, #8e0000 100%);
                    }

                    .offers-stats__card--deleted {
                        background: linear-gradient(135deg, #90a4ae 0%, #607d8b 60%, #455a64 100%);
                    }

                    .offers-stats__icon {
                        position: relative;
                        z-index: 1;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 2.4rem;
                        height: 2.4rem;
                        border-radius: .7rem;
                        background: rgba(255, 255, 255, .2);
                        font-size: 1.05rem;
                        margin-bottom: .55rem;
                    }

                    .offers-stats__value {
                        position: relative;
                        z-index: 1;
                        font-size: 1.55rem;
                        font-weight: 800;
                        line-height: 1.1;
                    }

                    .offers-stats__label {
                        position: relative;
                        z-index: 1;
                        font-size: .78rem;
                        font-weight: 600;
                        opacity: .92;
                        margin-top: .15rem;
                    }
                </style>
                <div class="offers-stats__grid">
                    <div class="offers-stats__card offers-stats__card--total">
                        <div class="offers-stats__icon"><i class="icon-stack2"></i></div>
                        <div class="offers-stats__value">{{ $all_offers_count }}</div>
                        <div class="offers-stats__label">{{ __('pages/community/offers/index.content.stats.all') }}</div>
                    </div>
                    <div class="offers-stats__card offers-stats__card--active">
                        <div class="offers-stats__icon"><i class="icon-checkmark3"></i></div>
                        <div class="offers-stats__value">{{ $active_offers_count }}</div>
                        <div class="offers-stats__label">{{ __('pages/community/offers/index.content.stats.active') }}</div>
                    </div>
                    <div class="offers-stats__card offers-stats__card--pending">
                        <div class="offers-stats__icon"><i class="icon-hourglass"></i></div>
                        <div class="offers-stats__value">{{ $unreviewed_offers_count }}</div>
                        <div class="offers-stats__label">{{ __('pages/community/offers/index.content.stats.pending') }}</div>
                    </div>
                    <div class="offers-stats__card offers-stats__card--expired">
                        <div class="offers-stats__icon"><i class="icon-calendar-cross"></i></div>
                        <div class="offers-stats__value">{{ $expired_offers_count }}</div>
                        <div class="offers-stats__label">{{ __('pages/community/offers/index.content.stats.expired') }}</div>
                    </div>
                    <div class="offers-stats__card offers-stats__card--unapproved">
                        <div class="offers-stats__icon"><i class="icon-cross"></i></div>
                        <div class="offers-stats__value">{{ $unapproved_offers_count }}</div>
                        <div class="offers-stats__label">{{ __('pages/community/offers/index.content.stats.unapproved') }}</div>
                    </div>
                    <div class="offers-stats__card offers-stats__card--deleted">
                        <div class="offers-stats__icon"><i class="icon-trash"></i></div>
                        <div class="offers-stats__value">{{ $deleted_offers_count }}</div>
                        <div class="offers-stats__label">{{ __('pages/community/offers/index.content.stats.deleted') }}</div>
                    </div>
                </div>
            </div>
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
