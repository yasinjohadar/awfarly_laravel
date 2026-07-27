<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">
    <div id="sidebar" class="sidebar-content">
        <div class="sidebar-section">
            <ul class="nav nav-sidebar" data-nav-type="accordion">
                {{--Dashboard--}}
                <li class="nav-item">
                    <a href="{{route('admin.dashboard')}}"
                       class="nav-link{{Request::routeIs('admin.dashboard') ? ' active' : ''}}">
                        <i class="icon-home4"></i>
                        <span>{{__('sidebar.dashboard')}}</span>
                    </a>
                </li>
                {{--/Dashboard --}}

                {{--Users --}}
                @canany(['admins.inquiry', 'admins.add','admins.roles.inquiry', 'admins.roles.add','customers.inquiry', 'customers.add','advertisers.inquiry', 'advertisers.add','business.types.inquiry', 'business.types.add','ratings.inquiry'])
                    <li id="users" class="nav-item-header">
                        <div class="text-uppercase font-size-xs line-height-xs">
                            {{__('sidebar.users.title')}}
                        </div>
                        <i class="icon-menu" title="{{__('sidebar.users.title')}}"></i>
                    </li>
                    @canany(['admins.inquiry', 'admins.add','admins.roles.inquiry', 'admins.roles.add',])
                        <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.admins.*') || Request::routeIs('admin.roles.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-user-tie"></i>
                                <span>{{__('sidebar.users.admins.title')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.users.admins.title')}}">
                                @can('admins.inquiry')
                                    <li class="nav-item">
                                        <a href="{{route('admin.admins.index')}}"
                                           class="nav-link{{Request::routeIs('admin.admins.index') ? ' active' : ''}}">
                                            {{__('sidebar.inquiry')}}
                                        </a>
                                    </li>
                                @endcan
                                @can('admins.add')
                                    <li class="nav-item">
                                        <a href="{{route('admin.admins.create')}}"
                                           class="nav-link{{Request::routeIs('admin.admins.create') ? ' active' : ''}}">
                                            {{__('sidebar.create')}}
                                        </a>
                                    </li>
                                @endcan
                                @canany(['admins.roles.inquiry', 'admins.roles.add',])
                                    <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.roles.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                                        <a class="nav-link cursor-pointer">
                                            <span>{{__('sidebar.users.admins.roles')}}</span>
                                        </a>
                                        <ul class="nav nav-group-sub">
                                            @can('admins.roles.inquiry')
                                                <li class="nav-item">
                                                    <a href="{{route('admin.roles.index')}}"
                                                       class="nav-link{{Request::routeIs('admin.roles.index') ? ' active' : ''}}">
                                                        {{__('sidebar.inquiry')}}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('admins.roles.add')
                                                <li class="nav-item">
                                                    <a href="{{route('admin.roles.create')}}"
                                                       class="nav-link{{Request::routeIs('admin.roles.create') ? ' active' : ''}}">
                                                        {{__('sidebar.create')}}
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcanany
                            </ul>
                        </li>
                    @endcanany
                    @canany(['customers.inquiry', 'customers.add',])
                        <li class="nav-item{{Request::routeIs('admin.customers.*') ? ' active' : ''}}">
                            <a href="{{route('admin.customers.index')}}"
                               class="nav-link{{Request::routeIs('admin.customers.*') ? ' active' : ''}}">
                                <i class="icon-users4"></i>
                                <span>{{__('sidebar.users.customers')}}</span>
                            </a>
                        </li>
                    @endcanany
                    @canany(['advertisers.inquiry', 'advertisers.add'])
                        <li class="nav-item{{(Request::routeIs('admin.advertisers.index') || Request::routeIs('admin.advertisers.create') || Request::routeIs('admin.advertisers.show') || Request::routeIs('admin.advertisers.reports')) ? ' active' : ''}}">
                            <a href="{{route('admin.advertisers.index')}}"
                               class="nav-link{{(Request::routeIs('admin.advertisers.index') || Request::routeIs('admin.advertisers.create') || Request::routeIs('admin.advertisers.show') || Request::routeIs('admin.advertisers.reports')) ? ' active' : ''}}">
                                <i class="icon-users"></i>
                                <span>{{__('sidebar.users.advertisers.title')}}</span>
                            </a>
                        </li>
                    @endcanany
                    @can('ratings.inquiry')
                        <li class="nav-item{{Request::routeIs('admin.advertisers.ratings') ? ' active' : ''}}">
                            <a href="{{route('admin.advertisers.ratings')}}"
                               class="nav-link{{Request::routeIs('admin.advertisers.ratings') ? ' active' : ''}}">
                                <i class="icon-stars"></i>
                                <span>{{__('sidebar.users.advertisers.ratings')}}</span>
                            </a>
                        </li>
                    @endcan
                    @canany(['business.types.inquiry', 'business.types.add'])
                        <li class="nav-item{{Request::routeIs('admin.advertisers.business.types.*') ? ' active' : ''}}">
                            <a href="{{route('admin.advertisers.business.types.index')}}"
                               class="nav-link{{Request::routeIs('admin.advertisers.business.types.*') ? ' active' : ''}}">
                                <i class="icon-briefcase"></i>
                                <span>{{__('sidebar.users.advertisers.business_types')}}</span>
                            </a>
                        </li>
                    @endcanany
                @endcanany
                {{--/Users--}}

                {{--Categories --}}
                @canany(['categories.inquiry', 'categories.add'])
                    <li id="categories" class="nav-item-header">
                        <div class="text-uppercase font-size-xs line-height-xs">
                            {{__('sidebar.categories')}}
                        </div>
                        <i class="icon-menu" title="{{__('sidebar.categories')}}"></i>
                    </li>
                    <li class="nav-item{{Request::routeIs('admin.categories.*') ? ' active' : ''}}">
                        <a href="{{route('admin.categories.index')}}"
                           class="nav-link{{Request::routeIs('admin.categories.*') ? ' active' : ''}}">
                            <i class="icon-stack2"></i>
                            <span>{{__('sidebar.categories')}}</span>
                        </a>
                    </li>
                @endcanany
                {{--/Categories--}}

                {{--Community--}}
                @canany(['posts.inquiry','posts.reported','comments.inquiry','comments.reported','offers.inquiry','offers.reported','proposals.inquiry','proposals.reported','chats.inquiry'])
                    <li id="community" class="nav-item-header">
                        <div class="text-uppercase font-size-xs line-height-xs">
                            {{__('sidebar.community.title')}}
                        </div>
                        <i class="icon-menu" title="{{__('sidebar.community.title')}}"></i>
                    </li>
                    @canany(['posts.inquiry','posts.reported','comments.inquiry','comments.reported'])
                        <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.community.posts.*') || Request::routeIs('admin.community.comments.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-insert-template"></i>
                                <span>{{__('sidebar.community.posts.title')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.community.posts.title')}}">
                                @can('posts.inquiry')
                                    <li class="nav-item">
                                        <a href="{{route('admin.community.posts.index')}}"
                                           class="nav-link{{Request::routeIs('admin.community.posts.index') || Request::routeIs('admin.community.posts.show') ? ' active' : ''}}">
                                            {{__('sidebar.inquiry')}}
                                        </a>
                                    </li>
                                @endcan
                                @can('posts.reported')
                                    <li class="nav-item">
                                        <a href="{{route('admin.community.posts.reports')}}"
                                           class="nav-link{{Request::routeIs('admin.community.posts.reports') ? ' active' : ''}}">
                                            {{__('sidebar.community.posts.reported')}}
                                        </a>
                                    </li>
                                @endcan

                                @canany(['comments.inquiry','comments.reported',])
                                    <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.community.comments.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                                        <a class="nav-link cursor-pointer">
                                            {{--<i class="icon-comments"></i>--}}
                                            <span>{{__('sidebar.community.posts.comments.title')}}</span>
                                        </a>
                                        <ul class="nav nav-group-sub"
                                            data-submenu-title="{{__('sidebar.community.posts.comments.title')}}">
                                            @can('comments.inquiry')
                                                <li class="nav-item">
                                                    <a href="{{route('admin.community.comments.index')}}"
                                                       class="nav-link{{Request::routeIs('admin.community.comments.index') || Request::routeIs('admin.community.comments.show') ? ' active' : ''}}">
                                                        {{__('sidebar.inquiry')}}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('comments.reported')
                                                <li class="nav-item">
                                                    <a href="{{route('admin.community.comments.reports')}}"
                                                       class="nav-link{{Request::routeIs('admin.community.comments.reports') ? ' active' : ''}}">
                                                        {{__('sidebar.community.posts.comments.reported')}}
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcanany
                            </ul>
                        </li>
                    @endcanany
                    @canany(['offers.inquiry','offers.reported','comments.inquiry','comments.reported'])
                        <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.community.offers.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-ticket"></i>
                                <span>{{__('sidebar.community.offers.title')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.community.offers.title')}}">
                                @can('offers.inquiry')
                                    <li class="nav-item">
                                        <a href="{{route('admin.community.offers.index')}}"
                                           class="nav-link{{Request::routeIs('admin.community.offers.index') || Request::routeIs('admin.community.offers.index') ? ' active' : ''}}">
                                            {{__('sidebar.inquiry')}}
                                        </a>
                                    </li>
                                @endcan
                                @can('offers.reported')
                                    <li class="nav-item">
                                        <a href="{{route('admin.community.offers.reports')}}"
                                           class="nav-link{{Request::routeIs('admin.community.offers.reports') ? ' active' : ''}}">
                                            {{__('sidebar.community.offers.reported')}}
                                        </a>
                                    </li>
                                @endcan
                                @canany(['comments.inquiry','comments.reported',])
                                    <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.community.offers.comments.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                                        <a class="nav-link cursor-pointer">
                                            {{--<i class="icon-comments"></i>--}}
                                            <span>{{__('sidebar.community.offers.comments.title')}}</span>
                                        </a>
                                        <ul class="nav nav-group-sub"
                                            data-submenu-title="{{__('sidebar.community.offers.comments.title')}}">
                                            @can('comments.inquiry')
                                                <li class="nav-item">
                                                    <a href="{{route('admin.community.offers.comments.index')}}"
                                                       class="nav-link{{Request::routeIs('admin.community.offers.comments.index') || Request::routeIs('admin.community.offers.comments.index') ? ' active' : ''}}">
                                                        {{__('sidebar.inquiry')}}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('comments.reported')
                                                <li class="nav-item">
                                                    <a href="{{route('admin.community.offers.comments.reports')}}"
                                                       class="nav-link{{Request::routeIs('admin.community.offers.comments.reports') ? ' active' : ''}}">
                                                        {{__('sidebar.community.offers.comments.reported')}}
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcanany
                                @canany(['ratings.inquiry'])
                                    <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.community.offers.ratings')) ? ' nav-item-expanded nav-item-open' : ''}}">
                                        <a class="nav-link cursor-pointer">
                                            <span>{{__('sidebar.community.offers.ratings')}}</span>
                                        </a>
                                        <ul class="nav nav-group-sub"
                                            data-submenu-title="{{__('sidebar.community.offers.ratings')}}">
                                            @can('ratings.inquiry')
                                                <li class="nav-item">
                                                    <a href="{{route('admin.community.offers.ratings')}}"
                                                       class="nav-link{{Request::routeIs('admin.community.offers.ratings') ? ' active' : ''}}">
                                                        {{__('sidebar.inquiry')}}
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                    @canany(['proposals.inquiry','proposals.reported',])
                        <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.community.proposals.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-cart"></i>
                                <span>{{__('sidebar.community.proposals.title')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.community.proposals.title')}}">
                                @can('proposals.inquiry')
                                    <li class="nav-item">
                                        <a href="{{route('admin.community.proposals.index')}}"
                                           class="nav-link{{Request::routeIs('admin.community.proposals.index') || Request::routeIs('admin.community.proposals.show') ? ' active' : ''}}">
                                            {{__('sidebar.inquiry')}}
                                        </a>
                                    </li>
                                @endcan
                                @can('proposals.reported')
                                    <li class="nav-item">
                                        <a href="{{route('admin.community.proposals.reports')}}"
                                           class="nav-link{{Request::routeIs('admin.community.proposals.reports') ? ' active' : ''}}">
                                            {{__('sidebar.community.proposals.reported')}}
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                    @canany(['posts.reported','comments.reported','offers.reported','proposals.reported'])
                        <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.community.reports.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-flag3"></i>
                                <span>{{__('sidebar.community.reports.title')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.community.reports.title')}}">
                                @can('posts.reported')
                                    <li class="nav-item">
                                        <a href="{{route('admin.community.reports.posts')}}"
                                           class="nav-link{{Request::routeIs('admin.community.reports.posts') ? ' active' : ''}}">
                                            {{__('sidebar.community.reports.posts')}}
                                        </a>
                                    </li>
                                @endcan
                                @can('comments.reported')
                                    <li class="nav-item">
                                        <a href="{{route('admin.community.reports.comments')}}"
                                           class="nav-link{{Request::routeIs('admin.community.reports.comments') ? ' active' : ''}}">
                                            {{__('sidebar.community.reports.comments')}}
                                        </a>
                                    </li>
                                @endcan
                                @can('offers.reported')
                                    <li class="nav-item">
                                        <a href="{{route('admin.community.reports.offers')}}"
                                           class="nav-link{{Request::routeIs('admin.community.reports.offers') ? ' active' : ''}}">
                                            {{__('sidebar.community.reports.offers')}}
                                        </a>
                                    </li>
                                @endcan
                                @can('proposals.reported')
                                    <li class="nav-item">
                                        <a href="{{route('admin.community.reports.proposals')}}"
                                           class="nav-link{{Request::routeIs('admin.community.reports.proposals') ? ' active' : ''}}">
                                            {{__('sidebar.community.reports.proposals')}}
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                    @can('chats.inquiry')
                        <li class="nav-item nav-item-submenu{{Request::routeIs('admin.community.chats.*') ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-bubbles"></i>
                                <span>{{__('sidebar.community.chats')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.community.chats')}}">
                                <li class="nav-item">
                                    <a href="{{route('admin.community.chats.index')}}"
                                       class="nav-link{{Request::routeIs('admin.community.chats.index') ? ' active' : ''}}">
                                        {{__('sidebar.inquiry')}}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                @endcanany
                {{--/Community --}}

                {{--Subscriptions --}}
                @canany(['packages.inquiry','packages.add','promotions.inquiry','promotions.add','payments.inquiry','income.inquiry'])
                    <li id="subscriptions" class="nav-item-header">
                        <div class="text-uppercase font-size-xs line-height-xs">
                            {{__('sidebar.subscriptions.title')}}
                        </div>
                        <i class="icon-menu" title="{{__('sidebar.subscriptions.title')}}"></i>
                    </li>
                    @canany(['packages.inquiry','packages.add'])
                        <li class="nav-item{{Request::routeIs('admin.subscriptions.packages.*') ? ' active' : ''}}">
                            <a href="{{route('admin.subscriptions.packages.index')}}"
                               class="nav-link{{Request::routeIs('admin.subscriptions.packages.*') ? ' active' : ''}}">
                                <i class="icon-package"></i>
                                <span>{{__('sidebar.subscriptions.packages')}}</span>
                            </a>
                        </li>
                    @endcanany
                    {{--
                    @canany(['promotions.inquiry', 'promotions.add'])
                    <li class="nav-item nav-item-submenu">
                        <a class="nav-link cursor-pointer">
                            <i class="icon-ticket"></i>
                            <span>{{__('sidebar.subscriptions.promotions')}}</span>
                        </a>
                        <ul class="nav nav-group-sub"
                            data-submenu-title="{{__('sidebar.subscriptions.promotions')}}">
                            @can('promotions.inquiry')
                            <li class="nav-item">
                                <a href="{{url('/')}}" class="nav-link">
                                    {{__('sidebar.inquiry')}}
                                </a>
                            </li>
                            @endcan
                            @can('promotions.add')
                            <li class="nav-item">
                                <a href="{{url('/')}}" class="nav-link">
                                    {{__('sidebar.create')}}
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany
                    --}}
                    @canany(['payments.inquiry'])
                        <li class="nav-item{{Request::routeIs('admin.subscriptions.payments.*') ? ' active' : ''}}">
                            <a href="{{route('admin.subscriptions.payments.index')}}"
                               class="nav-link{{Request::routeIs('admin.subscriptions.payments.*') ? ' active' : ''}}">
                                <i class="icon-cart4"></i>
                                <span>{{__('sidebar.subscriptions.payments.title')}}</span>
                            </a>
                        </li>
                    @endcanany
                    {{--
                    @canany(['income.inquiry'])
                        <li class="nav-item nav-item-submenu">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-coin-dollar"></i>
                                <span>{{__('sidebar.subscriptions.income')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.subscriptions.income')}}">
                                @can('income.inquiry')
                                    <li class="nav-item">
                                        <a href="{{url('/')}}" class="nav-link">
                                            {{__('sidebar.inquiry')}}
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany--}}
                @endcanany
                {{--/Subscriptions--}}

                {{--Requests --}}
                @canany(['requests.contact.us','requests.username.change',])
                    <li id="requests" class="nav-item-header">
                        <div class="text-uppercase font-size-xs line-height-xs">
                            {{__('sidebar.requests.title')}}
                        </div>
                        <i class="icon-menu" title="{{__('sidebar.requests.title')}}"></i>
                    </li>
                    <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.requests.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                        <a class="nav-link cursor-pointer">
                            <i class="icon-mailbox"></i>
                            <span>{{__('sidebar.requests.title')}}</span>
                        </a>
                        <ul class="nav nav-group-sub"
                            data-submenu-title="{{__('sidebar.requests.title')}}">
                            @can('requests.contact.us')
                                <li class="nav-item">
                                    <a href="{{route('admin.requests.contact-us.index')}}"
                                       class="nav-link{{Request::routeIs('admin.requests.contact-us.index') ? ' active' : ''}}">
                                        {{__('sidebar.requests.contact-us')}}
                                    </a>
                                </li>
                            @endcan
                            @can('requests.username.change')
                                <li class="nav-item">
                                    <a href="{{route('admin.requests.change-name.index')}}"
                                       class="nav-link{{Request::routeIs('admin.requests.change-name.index') ? ' active' : ''}}">
                                        {{__('sidebar.requests.username-change')}}
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                {{--/Requests --}}

                {{--Pages--}}
                @canany(['pages.inquiry','pages.add',])
                    <li id="pages" class="nav-item-header">
                        <div class="text-uppercase font-size-xs line-height-xs">
                            {{__('sidebar.pages')}}
                        </div>
                        <i class="icon-menu" title="{{__('sidebar.pages')}}"></i>
                    </li>
                    <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.pages.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                        <a class="nav-link cursor-pointer">
                            <i class="icon-file-text"></i>
                            <span>{{__('sidebar.pages')}}</span>
                        </a>
                        <ul class="nav nav-group-sub"
                            data-submenu-title="{{__('sidebar.pages')}}">
                            @can('pages.inquiry')
                                <li class="nav-item">
                                    <a href="{{route('admin.pages.index')}}"
                                       class="nav-link{{Request::routeIs('admin.pages.index') ? ' active' : ''}}">
                                        {{__('sidebar.inquiry')}}
                                    </a>
                                </li>
                            @endcan
                            {{--
                            @can('pages.add')
                            <li class="nav-item">
                                <a href="{{url('/')}}" class="nav-link">
                                    {{__('sidebar.create')}}
                                </a>
                            </li>
                            @endcan
                            --}}
                        </ul>
                    </li>
                @endcanany
                {{--/Pages--}}

                {{--Advertisements--}}
                @canany(['advertisements.inquiry', 'advertisements.add'])
                    <li id="advertisements" class="nav-item-header">
                        <div class="text-uppercase font-size-xs line-height-xs">
                            {{__('sidebar.advertisements.title')}}
                        </div>
                        <i class="icon-menu" title="{{__('sidebar.advertisements.title')}}"></i>
                    </li>
                    <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.advertisements.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                        <a class="nav-link cursor-pointer">
                            <i class="icon-newspaper"></i>
                            <span>{{__('sidebar.advertisements.targeted-advertisements')}}</span>
                        </a>
                        <ul class="nav nav-group-sub"
                            data-submenu-title="{{__('sidebar.advertisements.targeted-advertisements')}}">
                            @can('advertisements.inquiry')
                                <li class="nav-item">
                                    <a href="{{route('admin.advertisements.index')}}"
                                       class="nav-link{{Request::routeIs('admin.advertisements.index') || Request::routeIs('admin.advertisements.edit') ? ' active' : ''}}">
                                        {{__('sidebar.inquiry')}}
                                    </a>
                                </li>
                            @endcan
                            @can('advertisements.add')
                                <li class="nav-item">
                                    <a href="{{route('admin.advertisements.create')}}"
                                       class="nav-link{{Request::routeIs('admin.advertisements.create') ? ' active' : ''}}">
                                        {{__('sidebar.create')}}
                                    </a>
                                </li>
                            @endcan
                            @can('comments.inquiry')
                                <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.advertisements.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                                    <a class="nav-link cursor-pointer">
                                        <span>{{__('sidebar.community.posts.comments.title')}}</span>
                                    </a>
                                    <ul class="nav nav-group-sub"
                                        data-submenu-title="{{__('sidebar.advertisements.targeted-advertisements')}}">
                                        <li class="nav-item">
                                            <a href="{{route('admin.advertisements.comments.index')}}"
                                               class="nav-link{{Request::routeIs('admin.advertisements.comments.index') ? ' active' : ''}}">
                                                {{__('sidebar.inquiry')}}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{route('admin.advertisements.comments.reports')}}"
                                               class="nav-link{{Request::routeIs('admin.advertisements.comments.reports') ? ' active' : ''}}">
                                                {{__('sidebar.community.posts.comments.reported')}}
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.slider.advertisements.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                        <a class="nav-link cursor-pointer">
                            <i class="icon-newspaper"></i>
                            <span>{{__('sidebar.advertisements.slider-advertisements')}}</span>
                        </a>
                        <ul class="nav nav-group-sub"
                            data-submenu-title="{{__('sidebar.advertisements.slider-advertisements')}}">
                            @can('advertisements.inquiry')
                                <li class="nav-item">
                                    <a href="{{route('admin.slider.advertisements.index')}}"
                                       class="nav-link{{Request::routeIs('admin.slider.advertisements.index') ? ' active' : ''}}">
                                        {{__('sidebar.inquiry')}}
                                    </a>
                                </li>
                            @endcan
                            @can('advertisements.add')
                                <li class="nav-item">
                                    <a href="{{route('admin.slider.advertisements.create')}}"
                                       class="nav-link{{Request::routeIs('admin.slider.advertisements.create') ? ' active' : ''}}">
                                        {{__('sidebar.create')}}
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.side.advertisements.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                        <a class="nav-link cursor-pointer">
                            <i class="icon-newspaper"></i>
                            <span>{{__('sidebar.advertisements.side-advertisements')}}</span>
                        </a>
                        <ul class="nav nav-group-sub"
                            data-submenu-title="{{__('sidebar.advertisements.side-advertisements')}}">
                            @can('advertisements.inquiry')
                                <li class="nav-item">
                                    <a href="{{route('admin.side.advertisements.index')}}"
                                       class="nav-link{{Request::routeIs('admin.side.advertisements.index') ? ' active' : ''}}">
                                        {{__('sidebar.inquiry')}}
                                    </a>
                                </li>
                            @endcan
                            @can('advertisements.add')
                                <li class="nav-item">
                                    <a href="{{route('admin.side.advertisements.create')}}"
                                       class="nav-link{{Request::routeIs('admin.side.advertisements.create') ? ' active' : ''}}">
                                        {{__('sidebar.create')}}
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                {{--/Advertisements--}}

                {{--Marketing Tools--}}
                @canany(['send.emails','send.sms','send.notifications','modal.inquiry'])
                    <li id="marketing-tools" class="nav-item-header">
                        <div class="text-uppercase font-size-xs line-height-xs">
                            {{__('sidebar.marketing.title')}}
                        </div>
                        <i class="icon-menu" title="{{__('sidebar.marketing.title')}}"></i>
                    </li>
                    @can('send.emails')
                        <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.send.emails.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-mail5"></i>
                                <span>{{__('sidebar.marketing.send-email')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.marketing.send-email')}}">
                                <li class="nav-item">
                                    <a href="{{route('admin.send.emails.index')}}"
                                       class="nav-link{{Request::routeIs('admin.send.emails.index') ? ' active' : ''}}">
                                        {{__('sidebar.marketing.send-email')}}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                    {{--@can('send.sms')
                        <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.send.sms.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-phone-plus"></i>
                                <span>{{__('sidebar.marketing.send-sms')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.marketing.send-sms')}}">
                                <li class="nav-item">
                                    <a href="{{route('admin.send.sms.index')}}"
                                       class="nav-link{{Request::routeIs('admin.send.sms.index') ? ' active' : ''}}">
                                        {{__('sidebar.marketing.send-sms')}}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan--}}
                    @can('send.notifications')
                        <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.send.notifications.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-bell-plus"></i>
                                <span>{{__('sidebar.marketing.send-notifications')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.marketing.send-notifications')}}">
                                <li class="nav-item">
                                    <a href="{{route('admin.send.notifications.index')}}"
                                       class="nav-link{{Request::routeIs('admin.send.notifications.index') ? ' active' : ''}}">
                                        {{__('sidebar.marketing.send-notifications')}}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                    @can('modal.inquiry')

                    <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.send.modals.index.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                        <a class="nav-link cursor-pointer">
                            <i class="icon-newspaper"></i>
                            <span>{{__('sidebar.marketing.modals')}}</span>
                        </a>
                        <ul class="nav nav-group-sub"
                            data-submenu-title="{{__('sidebar.marketing.modals')}}">
                            @can('modal.inquiry')
                                <li class="nav-item">
                                    <a href="{{route('admin.send.modals.index')}}"
                                       class="nav-link{{Request::routeIs('admin.send.modals.index') ? ' active' : ''}}">
                                        {{__('sidebar.marketing.show')}}
                                    </a>
                                </li>
                            @endcan
                            @can('modal.add')
                                <li class="nav-item">
                                    <a href="{{route('admin.send.modals.create')}}"
                                       class="nav-link{{Request::routeIs('admin.send.modals.create') ? ' active' : ''}}">
                                        {{__('sidebar.marketing.create')}}
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>


                    @endcan
                @endcanany
                {{--/Marketing Tools--}}

                {{--System--}}
                @canany(['settings.inquiry', 'export.database', 'logs.inquiry', 'countries.inquiry', 'countries.add', 'governorates.inquiry', 'governorates.add', 'cities.inquiry', 'cities.add'])
                    <li id="system" class="nav-item-header">
                        <div class="text-uppercase font-size-xs line-height-xs">
                            {{__('sidebar.system.title')}}
                        </div>
                        <i class="icon-menu" title="{{__('sidebar.system.title')}}"></i>
                    </li>
                    @canany(['settings.inquiry', 'countries.inquiry', 'countries.add', 'governorates.inquiry', 'governorates.add', 'cities.inquiry', 'cities.add'])
                        <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.system.settings.index') || Request::routeIs('admin.countries.*') || Request::routeIs('admin.governorates.*') || Request::routeIs('admin.cities.*')) ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-cog"></i>
                                <span>{{__('sidebar.system.settings.title')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.system.settings.title')}}">
                                @can('settings.inquiry')
                                    <li class="nav-item">
                                        <a href="{{route('admin.system.settings.index', 'general')}}"
                                           class="nav-link{{(isset($settingType) && $settingType === 'general') ? ' active' : ''}}">
                                            {{__('sidebar.system.settings.general')}}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{route('admin.system.settings.index', 'chat')}}"
                                           class="nav-link{{(isset($settingType) && $settingType === 'chat') ? ' active' : ''}}">
                                            {{__('sidebar.system.settings.chat')}}
                                        </a>
                                    </li>
                                    <li class="nav-item nav-item-submenu{{(isset($settingType) && in_array($settingType, ['posts', 'offers', 'proposals'])) ? ' nav-item-expanded nav-item-open' : ''}}">
                                        <a class="nav-link cursor-pointer">
                                            <span>{{__('sidebar.system.settings.community')}}</span>
                                        </a>
                                        <ul class="nav nav-group-sub">
                                            <li class="nav-item">
                                                <a href="{{route('admin.system.settings.index', 'posts')}}"
                                                   class="nav-link{{(isset($settingType) && $settingType === 'posts') ? ' active' : ''}}">
                                                    {{__('sidebar.system.settings.posts')}}
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{route('admin.system.settings.index', 'offers')}}"
                                                   class="nav-link{{(isset($settingType) && $settingType === 'offers') ? ' active' : ''}}">
                                                    {{__('sidebar.system.settings.offers')}}
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{route('admin.system.settings.index', 'proposals')}}"
                                                   class="nav-link{{(isset($settingType) && $settingType === 'proposals') ? ' active' : ''}}">
                                                    {{__('sidebar.system.settings.proposals')}}
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{route('admin.system.settings.index', 'users')}}"
                                           class="nav-link{{(isset($settingType) && $settingType === 'users') ? ' active' : ''}}">
                                            {{__('sidebar.system.settings.users')}}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{route('admin.system.settings.index', 'maintenance')}}"
                                           class="nav-link{{(isset($settingType) && $settingType === 'maintenance') ? ' active' : ''}}">
                                            {{__('sidebar.system.settings.maintenance')}}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{route('admin.system.settings.index', 'apps')}}"
                                           class="nav-link{{(isset($settingType) && $settingType === 'apps') ? ' active' : ''}}">
                                            {{__('sidebar.system.settings.apps')}}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{route('admin.system.settings.index', 'social')}}"
                                           class="nav-link{{(isset($settingType) && $settingType === 'social') ? ' active' : ''}}">
                                            {{__('sidebar.system.settings.social')}}
                                        </a>
                                    </li>
                                @endcan
                                @canany(['countries.inquiry', 'countries.add'])
                                    <li class="nav-item">
                                        <a href="{{route('admin.countries.index')}}"
                                           class="nav-link{{Request::routeIs('admin.countries.*') ? ' active' : ''}}">
                                            {{__('sidebar.countries')}}
                                        </a>
                                    </li>
                                @endcanany
                                @canany(['governorates.inquiry', 'governorates.add'])
                                    <li class="nav-item">
                                        <a href="{{route('admin.governorates.index')}}"
                                           class="nav-link{{Request::routeIs('admin.governorates.*') ? ' active' : ''}}">
                                            {{__('sidebar.governorates')}}
                                        </a>
                                    </li>
                                @endcanany
                                @canany(['cities.inquiry', 'cities.add'])
                                    <li class="nav-item">
                                        <a href="{{route('admin.cities.index')}}"
                                           class="nav-link{{Request::routeIs('admin.cities.*') ? ' active' : ''}}">
                                            {{__('sidebar.cities')}}
                                        </a>
                                    </li>
                                @endcanany
                            </ul>
                        </li>
                    @endcanany
                    {{--
                    @can('export.database')
                    <li class="nav-item nav-item-submenu">
                        <a class="nav-link cursor-pointer">
                            <i class="icon-database-add"></i>
                            <span>{{__('sidebar.system.backup.title')}}</span>
                        </a>
                        <ul class="nav nav-group-sub"
                            data-submenu-title="{{__('sidebar.system.backup.title')}}">
                            <li class="nav-item">
                                <a href="{{url('/')}}" class="nav-link">
                                    {{__('sidebar.system.backup.export-db')}}
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan
                    --}}
                    @can('logs.inquiry')
                        <li class="nav-item nav-item-submenu{{(Request::routeIs('admin.system.logs.index')) ? ' nav-item-expanded nav-item-open' : ''}}">
                            <a class="nav-link cursor-pointer">
                                <i class="icon-archive"></i>
                                <span>{{__('sidebar.system.logs.title')}}</span>
                            </a>
                            <ul class="nav nav-group-sub"
                                data-submenu-title="{{__('sidebar.system.logs.title')}}">
                                <li class="nav-item">
                                    <a href="{{route('admin.system.logs.index')}}"
                                       class="nav-link{{Request::routeIs('admin.system.logs.index') ? ' active' : ''}}">
                                        {{__('sidebar.system.logs.admins-actions')}}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan
                @endcanany
                {{--/System--}}
            </ul>
        </div>
    </div>
</div>

@push('scripts')
    @unless (Request::routeIs('admin.dashboard'))
        @isset($alias)
            <script>
                $('#sidebar').ready(function () {
                    let sidebar = document.getElementById('sidebar');
                    let sectionTop = document.getElementById('{{$alias}}').offsetTop;
                    sidebar.scrollTo({
                        top: sectionTop + 90,
                    });
                })
            </script>
        @endisset
    @endunless
@endpush
