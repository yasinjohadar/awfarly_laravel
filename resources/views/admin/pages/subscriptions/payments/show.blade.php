<div>
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setPaymentId', null)">{{__('pages/subscriptions/payments/show.content.back')}}</button>
        <button title="Edit" @cannot('payments.edit') disabled
                @endcannot  wire:click="showEditModal({{ $payment_id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-pencil7"></i>
        </button>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/payments/show.content.package_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$paymentData->package_id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/payments/show.content.package_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($paymentData->package->name)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/payments/show.content.advertiser_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$paymentData->advertiser_id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/payments/show.content.advertiser_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($paymentData->advertiser->name)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/payments/show.content.starts_at')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$paymentData->starts_at ? \Carbon\Carbon::make($paymentData->starts_at)->format('Y-m-d h:i A') : '-'}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/payments/show.content.ends_at')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$paymentData->ends_at ? \Carbon\Carbon::make($paymentData->ends_at)->format('Y-m-d h:i A') : '-'}}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/payments/show.content.is_active')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$paymentData->is_active ? __('pages/subscriptions/payments/show.content.boolean.yes') :
                        __('pages/subscriptions/payments/show.content.boolean.no')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/payments/show.content.is_ended')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$paymentData->is_ended ? __('pages/subscriptions/payments/show.content.boolean.yes') :
                        __('pages/subscriptions/payments/show.content.boolean.no')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/payments/show.content.is_current')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$paymentData->is_current ? __('pages/subscriptions/payments/show.content.boolean.yes') :
                        __('pages/subscriptions/payments/show.content.boolean.no')}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/subscriptions/payments/show.content.deleted_at')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$paymentData->deleted_at ? \Carbon\Carbon::make($paymentData->deleted_at)->format('Y-m-d h:i A') : '-'}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('modals.subscriptions.payments.edit')
</div>
