<div class="card">
    <div class="card-header">
        @if($comment_id)
            <h5 class="card-title">{!! __('pages/community/offers/comments/reports/show.content.title', ['id' => $comment_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/community/offers/comments/reports/reports.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($comment_id)
            @livewire('community.offers.comments.reports.community-reported-offers-comment-inquiry-component', ['comment_id' => $comment_id, 'active' => $page_type], key($comment_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/community/offers/comments/reports/index.content.tabs.all', ['count' => $all_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('solved')"
                                class="nav-link bg-transparent{{$page_type === 'solved' ? ' active' : ''}}">
                            {{__('pages/community/offers/comments/reports/index.content.tabs.solved', ['count' => $solved_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('pending')"
                                class="nav-link bg-transparent{{$page_type === 'pending' ? ' active' : ''}}">
                            {{__('pages/community/offers/comments/reports/index.content.tabs.pending', ['count' => $pending_reports_count])}}
                        </button>
                    </li>
                </ul>

                @livewire('community.offers.comments.reports.community-reported-offers-comments-inquiry-component')
            </div>
        @endif
    </div>
</div>

@push('scripts')
@endpush
