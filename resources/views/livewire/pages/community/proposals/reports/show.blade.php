<div>
    <div class="form-group" x-data="{show: false}">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setProposalId', null)">{{__('pages/community/proposals/reports/show.content.back')}}</button>
        <button title="Edit" @cannot('proposals.reported') disabled
                @endcannot wire:click="showSolveModal({{ $proposal_id }})"
                class="btn btn-primary mx-1">
            {{__('pages/community/proposals/reports/show.content.solve')}}
        </button>
        @if(!$proposal->deleted_at)
            <button title="Edit" @cannot('proposals.reported') disabled
                    @endcannot wire:click="showDeleteModal({{ $proposal_id }})"
                    class="btn btn-danger mx-1">
                {{__('pages/community/proposals/reports/show.content.delete')}}
            </button>
        @endif
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/reports/show.content.proposal_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/reports/show.content.advertiser_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal->advertiser->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/reports/show.content.advertiser_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal->advertiser->name}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/reports/show.content.customer_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal->user->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/reports/show.content.customer_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal->user->name}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/reports/show.content.deleted_at')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal->deleted_at ?? '-'}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/reports/show.content.status')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{($proposal->deleted_at) ? __('pages/community/proposals/reports/show.content.solved') : __('pages/community/proposals/reports/show.content.unsolved')}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/community/proposals/reports/show.content.content')}}</div>
                <div class="text-secondary">
                    {!! $proposal->content !!}
                </div>
            </div>
            @if($proposal->answer)
                <div class="col-md-12 mt-5">
                    <div class="font-weight-bold">{{__('pages/community/proposals/reports/show.content.answer')}}</div>
                    <div class="text-secondary">
                        {!! $proposal->answer !!}
                    </div>
                </div>
            @endif
            @if(count($proposal['media']) > 0)
                <div class="media justify-content-center align-items-center">
                    <div id="animated-thumbnails-gallery" class="text-center" data-id="post-{{$proposal['id']}}">
                        @foreach($proposal['media'] as $media)
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
    @if(count($proposal['media']) > 0)
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
    {{--@include('modals.community.proposals.answers.edit')--}}
    @livewire('community.proposals.reports.community-reported-proposal-show-component', ['proposal_id' => $proposal_id], key($proposal_id))
    @include('modals.community.proposals.reports.delete')
    @include('modals.community.proposals.reports.solve')
</div>

