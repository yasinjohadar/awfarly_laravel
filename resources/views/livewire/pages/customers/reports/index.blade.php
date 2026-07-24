<div class="card">
    <div class="card-header">
        @if($customer_id)
            <h5 class="card-title">{!! __('pages/customers/reports/show.content.title', ['id' => $customer_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/customers/reports/reports.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($customer_id)
            @livewire('customers.reports.reported-customer-inquiry-component', ['customer_id' => $customer_id, 'active' => $page_type], key($customer_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/customers/reports/index.content.tabs.all', ['count' => $all_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('solved')"
                                class="nav-link bg-transparent{{$page_type === 'solved' ? ' active' : ''}}">
                            {{__('pages/customers/reports/index.content.tabs.solved', ['count' => $solved_reports_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('pending')"
                                class="nav-link bg-transparent{{$page_type === 'pending' ? ' active' : ''}}">
                            {{__('pages/customers/reports/index.content.tabs.pending', ['count' => $pending_reports_count])}}
                        </button>
                    </li>
                </ul>

                @livewire('customers.reports.reported-customers-inquiry-component')
            </div>
        @endif
    </div>
</div>

@push('scripts')
@endpush
