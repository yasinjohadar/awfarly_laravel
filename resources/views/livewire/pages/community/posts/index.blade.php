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
            <div class="posts-stats">
                <style>
                    .posts-stats__grid {
                        display: grid;
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                        gap: 1rem;
                        margin-bottom: 1.5rem;
                    }

                    @media (max-width: 991px) {
                        .posts-stats__grid {
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }
                    }

                    .posts-stats__card {
                        position: relative;
                        overflow: hidden;
                        border-radius: 1rem;
                        padding: 1.1rem 1.25rem;
                        color: #fff;
                        box-shadow: 0 8px 22px rgba(15, 23, 42, .1);
                    }

                    .posts-stats__card::after {
                        content: "";
                        position: absolute;
                        inset: auto -1.5rem -2rem auto;
                        width: 6.5rem;
                        height: 6.5rem;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, .12);
                    }

                    .posts-stats__card--total {
                        background: linear-gradient(135deg, #42a5f5 0%, #2e86d6 60%, #1d599f 100%);
                    }

                    .posts-stats__card--approved {
                        background: linear-gradient(135deg, #66bb6a 0%, #2e7d32 60%, #1b5e20 100%);
                    }

                    .posts-stats__card--pending {
                        background: linear-gradient(135deg, #ffa726 0%, #fb8c00 60%, #ef6c00 100%);
                    }

                    .posts-stats__card--deleted {
                        background: linear-gradient(135deg, #90a4ae 0%, #607d8b 60%, #455a64 100%);
                    }

                    .posts-stats__icon {
                        position: relative;
                        z-index: 1;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        width: 2.6rem;
                        height: 2.6rem;
                        border-radius: .75rem;
                        background: rgba(255, 255, 255, .2);
                        font-size: 1.2rem;
                        margin-bottom: .6rem;
                    }

                    .posts-stats__value {
                        position: relative;
                        z-index: 1;
                        font-size: 1.7rem;
                        font-weight: 800;
                        line-height: 1.1;
                    }

                    .posts-stats__label {
                        position: relative;
                        z-index: 1;
                        font-size: .85rem;
                        font-weight: 600;
                        opacity: .92;
                        margin-top: .15rem;
                    }
                </style>
                <div class="posts-stats__grid">
                    <div class="posts-stats__card posts-stats__card--total">
                        <div class="posts-stats__icon"><i class="icon-stack2"></i></div>
                        <div class="posts-stats__value">{{ $all_posts_count }}</div>
                        <div class="posts-stats__label">{{ __('pages/community/posts/index.content.stats.all') }}</div>
                    </div>
                    <div class="posts-stats__card posts-stats__card--approved">
                        <div class="posts-stats__icon"><i class="icon-checkmark3"></i></div>
                        <div class="posts-stats__value">{{ $active_posts_count }}</div>
                        <div class="posts-stats__label">{{ __('pages/community/posts/index.content.stats.approved') }}</div>
                    </div>
                    <div class="posts-stats__card posts-stats__card--pending">
                        <div class="posts-stats__icon"><i class="icon-hourglass"></i></div>
                        <div class="posts-stats__value">{{ $unreviewed_posts_count }}</div>
                        <div class="posts-stats__label">{{ __('pages/community/posts/index.content.stats.pending') }}</div>
                    </div>
                    <div class="posts-stats__card posts-stats__card--deleted">
                        <div class="posts-stats__icon"><i class="icon-trash"></i></div>
                        <div class="posts-stats__value">{{ $deleted_posts_count }}</div>
                        <div class="posts-stats__label">{{ __('pages/community/posts/index.content.stats.deleted') }}</div>
                    </div>
                </div>
            </div>
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

