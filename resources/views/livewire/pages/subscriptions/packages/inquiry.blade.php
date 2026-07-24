<div class="card">
    <div class="card-header">
        @if($package_id)
            <h5 class="card-title">{!! __('pages/subscriptions/packages/show.content.title', ['id' => $package_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/subscriptions/packages/inquiry.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($package_id)
            @livewire('subscriptions.packages.packages-show-component', ['package_id' => $package_id], key($package_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/subscriptions/packages/index.content.tabs.all', ['count' => $all_packages_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('active')"
                                class="nav-link bg-transparent{{$page_type === 'active' ? ' active' : ''}}">
                            {{__('pages/subscriptions/packages/index.content.tabs.active', ['count' => $active_packages_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('inactive')"
                                class="nav-link bg-transparent{{$page_type === 'inactive' ? ' active' : ''}}">
                            {{__('pages/subscriptions/packages/index.content.tabs.inactive', ['count' => $inactive_packages_count])}}
                        </button>
                    </li>
                </ul>
                @livewire('subscriptions.packages.packages-inquiry-component', ['activeNumberFilters' => $activeNumberFilters ?? []])
            </div>
        @endif
    </div>
</div>

