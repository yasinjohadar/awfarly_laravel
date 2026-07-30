<div class="card">
    <div class="card-header">
        @if($contact_id)
            <h5 class="card-title mb-0">{{ __('pages/requests/contact-us/index.content.title') }}</h5>
        @else
            <h5 class="card-title">{{__('pages/requests/contact-us/index.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($contact_id)
            @livewire('requests.contact-us.requests-contact-us-show-component', ['contact_id' => $contact_id], key($contact_id))
        @else
            <ul class="nav nav-tabs justify-content-center">
                <li class="nav-item">
                    <button wire:click="changeActiveTab('all')"
                            class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                        {{__('pages/requests/contact-us/index.content.all', ['count' => $all_count])}}
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="changeActiveTab('read')"
                            class="nav-link bg-transparent{{$page_type === 'read' ? ' active' : ''}}">
                        {{__('pages/requests/contact-us/index.content.read', ['count' => $read_count])}}
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="changeActiveTab('unread')"
                            class="nav-link bg-transparent{{$page_type === 'unread' ? ' active' : ''}}">
                        {{__('pages/requests/contact-us/index.content.unread', ['count' => $unread_count])}}
                    </button>
                </li>
            </ul>
            @livewire('requests.contact-us.requests-contact-us-inquiry-component')
        @endif
    </div>
</div>

