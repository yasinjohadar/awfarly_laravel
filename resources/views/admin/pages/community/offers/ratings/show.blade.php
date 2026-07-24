<div>
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setRatingId', null)">{{__('pages/community/offers/ratings/inquiry.content.back')}}</button>
        <button title="Edit" @cannot('ratings.approve') disabled
                @endcannot  wire:click="showEditModal({{ $rating_id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-pencil7"></i>
        </button>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/ratings/inquiry.content.rating_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$rating->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/ratings/inquiry.content.offer_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$rating->offer_id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/ratings/inquiry.content.rate')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$rating->rate}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/ratings/inquiry.content.status')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($rating->status)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/ratings/inquiry.content.user_type')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($rating->user->user_type)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/ratings/inquiry.content.user_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$rating->user->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/ratings/inquiry.content.user_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$rating->user->name}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/community/offers/ratings/inquiry.content.comment')}}</div>
                <div class="text-secondary">
                    {!! $rating->comment !!}
                </div>
            </div>
        </div>
    </div>
    @include('modals.users.advertisers.rating.edit')
</div>
