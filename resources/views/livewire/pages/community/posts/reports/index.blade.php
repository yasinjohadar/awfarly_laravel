<div class="card">
    <div class="card-header">
        @if($post_id)
            <h5 class="card-title">{!! __('pages/community/posts/reports/show.content.title', ['id' => $post_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/community/posts/reports/reports.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($post_id)
            @livewire('community.posts.reports.community-reported-post-inquiry-component', ['post_id' => $post_id, 'active' => $page_type], key($post_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/community/posts/reports/index.content.tabs.all', ['count' => $all_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('solved')"
                                class="nav-link bg-transparent{{$page_type === 'solved' ? ' active' : ''}}">
                            {{__('pages/community/posts/reports/index.content.tabs.solved', ['count' => $solved_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('pending')"
                                class="nav-link bg-transparent{{$page_type === 'pending' ? ' active' : ''}}">
                            {{__('pages/community/posts/reports/index.content.tabs.pending', ['count' => $pending_reports_count])}}
                        </button>
                    </li>
                </ul>

                @livewire('community.posts.reports.community-reported-posts-inquiry-component')
            </div>
        @endif
    </div>
</div>

@push('scripts')
@endpush
