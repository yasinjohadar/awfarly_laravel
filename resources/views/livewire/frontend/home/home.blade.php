<div>
    @forelse($posts as $post)
        @isset($post['isAdvertisement'])
            <div class="card" data-id="post-{{$post['id']}}">
                <div class="card-header border-0">
                    <div class="d-flex">
                        <div class="mr-2">
                            <img class="rounded-circle border" height="48" width="48"
                                 alt="{{$post['advertiserName']}}"
                                 title="{{$post['advertiserName']}}"
                                 src="{{$post['advertiserImage']}}">
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <p class="font-weight-bold mb-0">{{$post['advertiserName'] ?? Settings::Get('site.name')}}</p>
                            </div>
                            <div class="col-12">
                                {{__('frontend/home/home.advertisement')}}
                                @if($post['advertiserUrl'])
                                    -
                                    <a href="{{$post['advertiserUrl']}}" target="_blank">
                                        {{__('frontend/home/home.redirect-to-advertisement')}}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="content" style="text-align: initial" dir="auto">
                        <div class="mb-2">
                            {{$post['content']}}
                        </div>
                        @if(count($post['media']) > 0)
                            <div class="media justify-content-center align-items-center">
                                <div id="animated-thumbnails-gallery" class="text-center inline-gallery-container"
                                     data-id="post-{{$post['id']}}">
                                    @foreach($post['media'] as $media)
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
            </div>
        @else
            <div itemscope itemtype="{{route('post.index', $post['id'])}}" class="card" data-id="post-{{$post['id']}}">
                <div class="card-header border-0">
                    <div class="d-flex">
                        <div class="mr-2{{$post['owner']['isElite'] ? ' elite' : ''}}">
                            <img class="rounded-circle{{$post['owner']['isElite'] ? '' : ' border'}}" height="48"
                                 width="48"
                                 alt="{{$post['owner']['name']}}"
                                 title="{{$post['owner']['name']}}"
                                 src="{{$post['owner']['imageUrl']}}">
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <p itemprop="author" class="font-weight-bold mb-0 d-inline">{{$post['owner']['businessTypeName'] ?? ""}} {{$post['owner']['name']}}</p>
                                <div class="small d-inline font-weight-bold"> - {{$post['createdAt']}}</div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-8">
                                        <li class="icon-location3"></li>
{{--                                        {{$post['owner']['country']}} - {{$post['owner']['city']}}--}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="content" style="text-align: initial" dir="auto">
                        <div summary="{{Str::limit($post['content'], 40)}}" class="mb-2">
                            {{$post['content']}}
                        </div>

                        @if(count($post['media']) > 0)
                            <div class="media justify-content-center align-items-center">
                                <div id="animated-thumbnails-gallery" class="text-center inline-gallery-container"
                                     data-id="post-{{$post['id']}}">
                                    @foreach($post['media'] as $media)
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
            </div>
        @endisset
    @empty
        <div itemscope class="card">
            <div class="card-body">
                {{__('frontend/home/home.posts.no_posts')}}
            </div>
        </div>
    @endforelse
    @if($has_more)
        <div class="text-center">
            <button class="btn btn-white" wire:loading.remove wire:key="load_more" wire:click="loadMore">
                <div summary="{{__('frontend/home/home.posts.load_more')}}">
                    {{__('frontend/home/home.posts.load_more')}}
                </div>
            </button>
            <i wire:key="loading_more" wire:loading.class="spinner-border text-dark mr-2"></i>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        let media = document.querySelectorAll('#animated-thumbnails-gallery');
        media.forEach(function (element) {
            let galleryId = element.getAttribute('data-id');
            lightGallery(element, {
                container: element,
                plugins: [lgZoom, lgThumbnail, lgAutoplay, lgFullscreen, lgPager, lgShare, lgVideo],
                hash: false,
                closable: false,
                showMaximizeIcon: true,
                slideDelay: 400,
                autoplayFirstVideo: false,
                appendSubHtmlTo: ".lg-item",
                thumbnail: true,
                animateThumb: true,
                zoomFromOrigin: true,
                allowMediaOverlap: true,
                toggleThumb: true,
                galleryId: galleryId,
                getCaptionFromTitleOrAlt: true,
            }).openGallery();
        })
        window.addEventListener('resetLightGallery', (event) => {

            /*updateSlideInstance.destroy()*/
            let $container = document.querySelectorAll('.lg-container');
            $container.forEach(function (element) {
                element.remove();
            })

            let media = document.querySelectorAll('#animated-thumbnails-gallery');
            media.forEach(function (element) {
                let galleryId = element.getAttribute('data-id');
                lightGallery(element, {
                    container: element,
                    plugins: [lgZoom, lgThumbnail, lgAutoplay, lgFullscreen, lgPager, lgShare, lgVideo],
                    closable: false,
                    showMaximizeIcon: true,
                    slideDelay: 400,
                    autoplayFirstVideo: false,
                    appendSubHtmlTo: ".lg-item",
                    thumbnail: true,
                    animateThumb: true,
                    zoomFromOrigin: true,
                    allowMediaOverlap: true,
                    toggleThumb: true,
                    galleryId: galleryId,
                    getCaptionFromTitleOrAlt: true,
                }).openGallery();
            })
        });
    </script>
@endpush
