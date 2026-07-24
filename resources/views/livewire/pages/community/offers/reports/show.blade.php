<div>
    <div class="form-group" x-data="{show: false}">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setOfferId', null)">{{__('pages/community/offers/reports/show.content.back')}}</button>
        <button title="Edit" @cannot('offers.reported') disabled
                @endcannot  wire:click="showSolveModal({{ $offer_id }})"
                class="btn btn-primary mx-1">
            {{__('pages/community/offers/reports/show.content.solve')}}
        </button>

        @if(!$offer->deleted_at)
            <button title="Edit" @cannot('offers.reported') disabled
                    @endcannot  wire:click="showDeleteModal({{ $offer_id }})"
                    class="btn btn-danger mx-1">
                {{__('pages/community/offers/reports/show.content.delete')}}
            </button>
        @endif
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/reports/show.content.offer_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$offer->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/reports/show.content.user_type')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($offer->advertiser->user_type)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/reports/show.content.user_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$offer->advertiser->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/reports/show.content.user_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$offer->advertiser->name}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/reports/show.content.views_count')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$offer->views_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/reports/show.content.likes_count')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$offer->likes_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/reports/show.content.comments_count')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$offer->comments_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/reports/show.content.deleted_at')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$offer->deleted_at ?? '-'}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/offers/reports/show.content.status')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{($offer->deleted_at) ? __('pages/community/offers/reports/show.content.solved') : __('pages/community/offers/reports/show.content.unsolved')}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/community/offers/reports/show.content.content')}}</div>
                <div class="text-secondary">
                    {!! $offer->content !!}
                </div>
            </div>
            @if(count($offer['media']) > 0)
                <div class="media justify-content-center align-items-center">
                    <div id="animated-thumbnails-gallery" class="text-center" data-id="post-{{$offer['id']}}">
                        @foreach($offer['media'] as $media)
                            @if($media['type'] === 'video')
                                <a data-lg-size="1920-1080"
                                   data-video='{"source": [{"src":"{{$media['mediaUrl']}}", "type":"{{$media['mimeType']}}"}], "attributes": {"preload": false, "controls": true}}'
                                   data-poster="{{$media['thumbnailImageUrl']}}"
                                   data-sub-html="{{$media['fileName']}}">
                                    <img class="img-fluid py-2 py-sm-0 cursor-pointer"
                                         src="{{$media['thumbnailImageUrl'] ?? $media['mediaUrl']}}"
                                         alt="{{$media['fileName']}}"/>
                                </a>
                            @else
                                <a href="{{$media['mediaUrl']}}" class="px-2"
                                   data-thumb="{{$media['thumbnailImageUrl']}}">
                                    <img class="img-fluid py-2 py-sm-0" width="200"
                                         src="{{$media['thumbnailImageUrl'] ?? $media['mediaUrl']}}"
                                         alt="{{$media['fileName']}}"/>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    @if(count($offer['media']) > 0)
        <script type="text/javascript">
            lightGallery(document.getElementById('animated-thumbnails-gallery'), {
                plugins: [lgZoom, lgThumbnail, lgAutoplay, lgComment, lgFullscreen, lgPager, lgRotate, lgShare, lgVideo],
                thumbnail: true,
                animateThumb: true,
                zoomFromOrigin: true,
                allowMediaOverlap: true,
                toggleThumb: true,
            });
        </script>
    @endif
    {{--@include('modals.community.offers.answers.edit')--}}
    @livewire('community.offers.reports.community-reported-offer-show-component', ['offer_id' => $offer_id], key($offer_id))
    @include('modals.community.offers.reports.delete')
    @include('modals.community.offers.reports.solve')
</div>

