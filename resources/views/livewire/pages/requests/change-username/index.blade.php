<div class="card">
    <div class="card-header">
        @if($request_id)
            <h5 class="card-title">{!! __('pages/requests/username-change/inquiry.content.title', ['id' => $request_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/requests/username-change/index.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($request_id)
            @livewire('requests.username-change.requests-username-change-show-component', ['request_id' => $request_id], key($request_id))
        @else
            <ul class="nav nav-tabs justify-content-center">
                <li class="nav-item">
                    <button wire:click="changeActiveTab('all')"
                            class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                        {{__('pages/requests/username-change/index.content.all', ['count' => $all_count])}}
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="changeActiveTab('approved')"
                            class="nav-link bg-transparent{{$page_type === 'approved' ? ' active' : ''}}">
                        {{__('pages/requests/username-change/index.content.approved', ['count' => $approved_count])}}
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="changeActiveTab('pending')"
                            class="nav-link bg-transparent{{$page_type === 'pending' ? ' active' : ''}}">
                        {{__('pages/requests/username-change/index.content.pending', ['count' => $pending_count])}}
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="changeActiveTab('declined')"
                            class="nav-link bg-transparent{{$page_type === 'declined' ? ' active' : ''}}">
                        {{__('pages/requests/username-change/index.content.declined', ['count' => $declined_count])}}
                    </button>
                </li>
            </ul>
            @livewire('requests.username-change.requests-username-change-inquiry-component')
        @endif
    </div>
</div>

