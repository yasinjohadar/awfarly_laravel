<li class="nav-item nav-item-dropdown-lg dropdown">
    <a href="#" class="navbar-nav-link navbar-nav-link-toggler dropdown-toggle"
       data-toggle="dropdown" aria-expanded="false">
        <i class="icon-bell2"></i>
        @if(($pending_posts_count + $pending_offers_count) > 0)
            <span class="badge badge-pill bg-danger">{{ $pending_posts_count + $pending_offers_count }}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <h6 class="dropdown-header">{{ __('navbar.notifications.title') }}</h6>
        <div class="dropdown-divider"></div>
        @can('posts.inquiry')
            @if($pending_posts_count > 0)
                <a href="{{ route('admin.community.posts.index', ['page_type' => 'unreviewed']) }}" class="dropdown-item">
                    <i class="icon-file-text2"></i>
                    {{ __('navbar.notifications.posts', ['count' => $pending_posts_count]) }}
                </a>
            @endif
        @endcan
        @can('offers.inquiry')
            @if($pending_offers_count > 0)
                <a href="{{ route('admin.community.offers.index', ['page_type' => 'unreviewed']) }}" class="dropdown-item">
                    <i class="icon-price-tags2"></i>
                    {{ __('navbar.notifications.offers', ['count' => $pending_offers_count]) }}
                </a>
            @endif
        @endcan
        @if(($pending_posts_count + $pending_offers_count) === 0)
            <span class="dropdown-item disabled">{{ __('navbar.notifications.empty') }}</span>
        @endif
    </div>
</li>
