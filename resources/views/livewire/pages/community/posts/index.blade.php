<div class="card">
    <div class="card-header">
        @if($post_id)
            <h5 class="card-title">{!! __('pages/community/posts/inquiry.content.title', ['id' => $post_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/community/posts/index.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($post_id)
            @livewire('community.posts.community-posts-show-component', ['post_id' => $post_id], key($post_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/community/posts/index.content.tabs.all', ['count' => $all_posts_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('unreviewed')"
                                class="nav-link bg-transparent{{$page_type === 'unreviewed' ? ' active' : ''}}">
                            {{__('pages/community/posts/index.content.tabs.unreviewed', ['count' => $unreviewed_posts_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('active')"
                                class="nav-link bg-transparent{{$page_type === 'active' ? ' active' : ''}}">
                            {{__('pages/community/posts/index.content.tabs.active', ['count' => $active_posts_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('deleted')"
                                class="nav-link bg-transparent{{$page_type === 'deleted' ? ' active' : ''}}">
                            {{__('pages/community/posts/index.content.tabs.deleted', ['count' => $deleted_posts_count])}}
                        </button>
                    </li>
                </ul>
                @livewire('community.posts.community-posts-inquiry-component', ['activeNumberFilters' => $activeNumberFilters ?? []])
            </div>
        @endif
    </div>
</div>

