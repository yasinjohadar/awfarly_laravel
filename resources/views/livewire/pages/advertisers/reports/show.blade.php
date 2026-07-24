<div>
    <div class="form-group" x-data="{show: false}">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setAdvertiserId', null)">{{__('pages/advertisers/reports/show.content.back')}}</button>

        @if($status !== 'solved')
            <button title="Edit" @cannot('advertisers.inquiry') disabled
                    @endcannot  wire:click="showSolveModal({{ $advertiser_id }})"
                    class="btn btn-primary mx-1">
                {{__('pages/advertisers/reports/show.content.solve')}}
            </button>
        @endif

        @if($status !== 'solved')
            <button title="Delete Post" @cannot('advertisers.inquiry') disabled
                    @endcannot wire:click="showDeleteModal({{ $advertiser_id }})"
                    class="btn btn-danger mx-1">
                {{__('pages/advertisers/reports/show.content.ban')}}
            </button>
        @endif
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisers/reports/show.content.advertiser_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$advertiser->id}}</div>
                </div>
                {{--<div class="row">
                    <div class="col-md-2">{{__('pages/advertisers/reports/show.content.user_type')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($advertiser->user_type)}}</div>
                </div>--}}
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisers/reports/show.content.user_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$advertiser->name}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisers/reports/show.content.status')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{($advertiser->status === 'banned') ? __('pages/advertisers/reports/show.content.solved') : __('pages/advertisers/reports/show.content.unsolved')}}</div>
                </div>
            </div>
        </div>
    </div>
    @livewire('advertisers.reports.reported-advertiser-show-component', ['advertiser_id' => $advertiser_id], key($advertiser_id))
    @include('modals.users.advertisers.reports.delete')
    @include('modals.users.advertisers.reports.solve')
</div>

