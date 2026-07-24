<div class="card">
    <div class="card-header">
        @if($proposal_id)
            <h5 class="card-title">{!! __('pages/community/proposals/inquiry.content.title', ['id' => $proposal_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/community/proposals/index.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($proposal_id)
            @livewire('community.proposals.community-proposals-show-component', ['proposal_id' => $proposal_id])
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/community/proposals/index.content.tabs.all', ['count' => $all_proposals_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('unanswered')"
                                class="nav-link bg-transparent{{$page_type === 'unanswered' ? ' active' : ''}}">
                            {{__('pages/community/proposals/index.content.tabs.unanswered', ['count' => $unanswered_proposals_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('answered')"
                                class="nav-link bg-transparent{{$page_type === 'answered' ? ' active' : ''}}">
                            {{__('pages/community/proposals/index.content.tabs.answered', ['count' => $answered_proposals_count])}}
                        </button>
                    </li>
                </ul>
                @livewire('community.proposals.community-proposals-inquiry-component', ['activeNumberFilters' => $activeNumberFilters ?? []])
            </div>
        @endif
    </div>
</div>


