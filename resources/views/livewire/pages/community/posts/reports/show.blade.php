<div>
    <div class="form-group" x-data="{show: false}">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setPostId', null)">{{__('pages/community/posts/reports/show.content.back')}}</button>

        @if($status !== 'solved')
            <button title="Edit" @cannot('posts.reported') disabled
                    @endcannot  wire:click="showSolveModal({{ $post_id }})"
                    class="btn btn-primary mx-1">
                {{__('pages/community/posts/reports/show.content.solve')}}
            </button>
        @endif

        @if(!$post->deleted_at && $status !== 'solved')
            <button title="Delete Post" @cannot('posts.reported') disabled
                    @endcannot wire:click="showDeleteModal({{ $post_id }})"
                    class="btn btn-danger mx-1">
                {{__('pages/community/posts/reports/show.content.delete')}}
            </button>
        @endif
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/reports/show.content.post_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/reports/show.content.user_type')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($post->user->user_type)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/reports/show.content.user_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->user->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/reports/show.content.user_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->user->name}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/reports/show.content.views_count')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->views_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/reports/show.content.likes_count')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->likes_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/reports/show.content.comments_count')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->comments_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/reports/show.content.deleted_at')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->deleted_at ?? '-'}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/reports/show.content.status')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{($post->deleted_at) ? __('pages/community/posts/reports/show.content.solved') : __('pages/community/posts/reports/show.content.unsolved')}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/community/posts/reports/show.content.content')}}</div>
                <div class="text-secondary">
                    {!! $post->content !!}
                </div>
            </div>
            @if(count($post['media']) > 0)
                <div class="col-md-12 mt-5">
                    <div class="font-weight-bold">{{__('pages/community/posts/reports/show.content.images')}}</div>
                    <ul id="animated-thumbnails-gallery" dir="ltr"
                        class="justified-gallery justify-content-center list-unstyled d-flex">
                        @foreach ($post['media'] as $index => $media)
                            <li data-id="{{$media['id']}}" data-src="{{$media['mediaUrl']}}" data-download-url="{{$media['downloadUrl']}}"
                                class="grid-square px-3 cursor-pointer position-relative">
                                @if($media['type'] === 'video')
                                    <a data-lg-size="1920-1080"
                                       data-video='{"source": [{"src":"{{$media['mediaUrl']}}", "type":"{{$media['mimeType']}}"}], "attributes": {"preload": false, "controls": true}}'
                                       data-poster="{{$media['thumbnailImageUrl']}}"
                                       data-sub-html="{{$media['fileName']}}">
                                        <img class="img-fluid py-2 py-sm-0 cursor-pointer" width="400"
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
                                <div
                                    class="font-weight-bold text-center">{{__('pages/community/posts/reports/show.content.image')}}{{$index+1}}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
    @if(count($post['media']) > 0)
        <script type="text/javascript">
            lightGallery(document.getElementById('animated-thumbnails-gallery'), {
                plugins: [lgZoom, lgThumbnail, lgAutoplay, lgComment, lgFullscreen, lgPager, lgRotate, lgShare, lgVideo],
                thumbnail: true,
                animateThumb: true,
                zoomFromOrigin: true,
                allowMediaOverlap: true,
                toggleThumb: true,
                hash: true,
                closable: true,
                showMaximizeIcon: false,
                slideDelay: 400,
                autoplayFirstVideo: false,
                getCaptionFromTitleOrAlt: true,
            });
        </script>
    @endif
    {{--@include('modals.community.posts.answers.edit')--}}
    @livewire('community.posts.reports.community-reported-post-show-component', ['post_id' => $post_id], key($post_id))
    @include('modals.community.posts.reports.delete')
    @include('modals.community.posts.reports.solve')
</div>

