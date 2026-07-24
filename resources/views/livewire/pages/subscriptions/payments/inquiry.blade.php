<div class="card">
    <div class="card-header">
        @if($payment_id)
            <h5 class="card-title">{!! __('pages/subscriptions/payments/show.content.title', ['id' => $payment_id]) !!}</h5>
        @else
            <h5 class="card-title">{{__('pages/subscriptions/payments/inquiry.content.title')}}</h5>
        @endif
    </div>
    <div class="card-body">
        @if($payment_id)
            @livewire('subscriptions.payments.payments-show-component', ['payment_id' => $payment_id], key($payment_id))
        @else
            <div class="form-group">
                <ul class="nav nav-tabs justify-content-center">
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('all')"
                                class="nav-link bg-transparent{{$page_type === 'all' ? ' active' : ''}}">
                            {{__('pages/subscriptions/payments/index.content.tabs.all', ['count' => $all_payments_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('active')"
                                class="nav-link bg-transparent{{$page_type === 'active' ? ' active' : ''}}">
                            {{__('pages/subscriptions/payments/index.content.tabs.active', ['count' => $active_payments_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('expired')"
                                class="nav-link bg-transparent{{$page_type === 'expired' ? ' active' : ''}}">
                            {{__('pages/subscriptions/payments/index.content.tabs.expired', ['count' => $expired_payments_count])}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="changeActiveTab('deleted')"
                                class="nav-link bg-transparent{{$page_type === 'deleted' ? ' active' : ''}}">
                            {{__('pages/subscriptions/payments/index.content.tabs.deleted', ['count' => $deleted_payments_count])}}
                        </button>
                    </li>
                </ul>
                @livewire('subscriptions.payments.payments-inquiry-component')
            </div>
        @endif
    </div>
</div>
