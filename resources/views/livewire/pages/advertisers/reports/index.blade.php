<div class="card">
    <div class="card-header">
        @if($advertiser_id)
            <h5 class="card-title">{!! __('pages/advertisers/reports/show.content.title', ['id' => $advertiser_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/advertisers/reports/reports.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($advertiser_id)
            @livewire('advertisers.reports.reported-advertiser-inquiry-component', ['advertiser_id' => $advertiser_id, 'active' => $page_type], key($advertiser_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/advertisers/reports/index.content.tabs.all', ['count' => $all_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('solved')"
                                class="nav-link bg-transparent{{$page_type === 'solved' ? ' active' : ''}}">
                            {{__('pages/advertisers/reports/index.content.tabs.solved', ['count' => $solved_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('pending')"
                                class="nav-link bg-transparent{{$page_type === 'pending' ? ' active' : ''}}">
                            {{__('pages/advertisers/reports/index.content.tabs.pending', ['count' => $pending_reports_count])}}
                        </button>
                    </li>
                </ul>

                @livewire('advertisers.reports.reported-advertisers-inquiry-component')
            </div>
        @endif
    </div>
</div>

@push('scripts')
@endpush
