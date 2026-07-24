<div>
    <div class="form-group" x-data="{show: false}">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setCustomerId', null)">{{__('pages/customers/reports/show.content.back')}}</button>

        @if($status !== 'solved')
            <button title="Edit" @cannot('customers.inquiry') disabled
                    @endcannot  wire:click="showSolveModal({{ $customer_id }})"
                    class="btn btn-primary mx-1">
                {{__('pages/customers/reports/show.content.solve')}}
            </button>
        @endif

        @if($status !== 'solved')
            <button title="Delete Post" @cannot('customers.inquiry') disabled
                    @endcannot wire:click="showDeleteModal({{ $customer_id }})"
                    class="btn btn-danger mx-1">
                {{__('pages/customers/reports/show.content.ban')}}
            </button>
        @endif
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/customers/reports/show.content.customer_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$customer->id}}</div>
                </div>
                {{--<div class="row">
                    <div class="col-md-2">{{__('pages/customers/reports/show.content.user_type')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($customer->user_type)}}</div>
                </div>--}}
                <div class="row">
                    <div class="col-md-2">{{__('pages/customers/reports/show.content.user_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$customer->name}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="row">
                    <div class="col-md-2">{{__('pages/customers/reports/show.content.status')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{($customer->status === 'banned') ? __('pages/customers/reports/show.content.solved') : __('pages/customers/reports/show.content.unsolved')}}</div>
                </div>
            </div>
        </div>
    </div>
    @livewire('customers.reports.reported-customer-show-component', ['customer_id' => $customer_id], key($customer_id))
    @include('modals.users.customers.reports.delete')
    @include('modals.users.customers.reports.solve')
</div>

