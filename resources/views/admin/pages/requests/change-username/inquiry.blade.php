<div>
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setRequestId', null)">{{__('pages/requests/username-change/inquiry.content.back')}}</button>
        @if($request->status ==='pending')
            <button title="Edit" @cannot('requests.username.change') disabled
                    @endcannot wire:click="showConfirmModal({{ $request_id }}, 'approved')"
                    class="btn btn-secondary mx-1">
                {{__('pages/requests/username-change/inquiry.content.approve')}}
            </button>
            <button title="Edit" @cannot('requests.username.change') disabled
                    @endcannot wire:click="showConfirmModal({{ $request_id }}, 'declined')"
                    class="btn btn-secondary mx-1">
                {{__('pages/requests/username-change/inquiry.content.decline')}}
            </button>
        @endif
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/username-change/inquiry.content.contact_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$request->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/username-change/inquiry.content.user_type')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{ucwords($request->user->user_type)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/username-change/inquiry.content.user_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$request->user->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/username-change/inquiry.content.user_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$request->user->name}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/username-change/inquiry.content.old_username')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$request->old_username}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/username-change/inquiry.content.new_username')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$request->new_username}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/username-change/inquiry.content.status')}}</div>
                    <div class="col-md-10 font-weight-bold">{{__("pages/requests/username-change/inquiry.content.{$request->status}")}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/requests/username-change/inquiry.content.created_at')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{\Carbon\Carbon::make($request->created_at)->format('Y-m-d h:i A')}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/requests/username-change/inquiry.content.reason')}}</div>
                <div class="text-secondary">
                    {!! $request->reason !!}
                </div>
            </div>
        </div>
    </div>
    @include('modals.requests.change-username.confirmation')
</div>
