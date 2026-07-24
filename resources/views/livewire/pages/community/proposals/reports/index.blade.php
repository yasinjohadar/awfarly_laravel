<div class="card">
    <div class="card-header">
        @if($proposal_id)
            <h5 class="card-title">{!! __('pages/community/proposals/reports/show.content.title', ['id' => $proposal_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/community/proposals/reports/reports.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($proposal_id)
            @livewire('community.proposals.reports.community-reported-proposal-inquiry-component', ['proposal_id' => $proposal_id, 'active' => $page_type], key($proposal_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/community/proposals/reports/index.content.tabs.all', ['count' => $all_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('solved')"
                                class="nav-link bg-transparent{{$page_type === 'solved' ? ' active' : ''}}">
                            {{__('pages/community/proposals/reports/index.content.tabs.solved', ['count' => $solved_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('pending')"
                                class="nav-link bg-transparent{{$page_type === 'pending' ? ' active' : ''}}">
                            {{__('pages/community/proposals/reports/index.content.tabs.pending', ['count' => $pending_reports_count])}}
                        </button>
                    </li>
                </ul>

                @livewire('community.proposals.reports.community-reported-proposals-inquiry-component')
            </div>
        @endif
    </div>
</div>

@push('scripts')
@endpush
