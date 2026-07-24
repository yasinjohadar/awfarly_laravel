<div class="form-group">
    <ul class="nav nav-tabs justify-content-center">
        <li class="nav-item">
            <button wire:click="changeActiveTab('all')"
                    class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                {{__('pages/community/offers/comments/index.content.tabs.all', ['count' => $all_comments_count])}}
            </button>
        </li>{{--
        <li class="nav-item">
            <button wire:click="changeActiveTab('active')"
                    class="nav-link bg-transparent{{$page_type === 'active' ? ' active' : ''}}">
                {{__('pages/community/offers/comments/index.content.tabs.active', ['count' => $active_comments_count])}}
            </button>
        </li>--}}
        <li class="nav-item">
            <button wire:click="changeActiveTab('deleted')"
                    class="nav-link bg-transparent{{$page_type === 'deleted' ? ' active' : ''}}">
                {{__('pages/community/offers/comments/index.content.tabs.deleted', ['count' => $deleted_comments_count])}}
            </button>
        </li>
    </ul>
    @livewire('community.offers.comments.community-offers-comments-inquiry-component', ['activeNumberFilters' => $activeNumberFilters ?? []])
</div>



