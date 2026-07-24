@section('meta-title', $offer['owner']['name'])
@section('meta-type', __('frontend/offer/offer.meta.type'))
@section('meta-url', route('offer.index', $offer['id']))
@if(count($offer['media']) > 0)
    @section('meta-image', $offer['media'][0]['mediaUrl'])
@endif
@section('meta-description', Str::limit($offer['content'], 120))
<div>
    <div itemscope itemtype="{{route('offer.index', $offer['id'])}}" class="card" data-id="offer-{{$offer['id']}}">
        <div class="card-header border-0">
            <div class="d-flex">
                <div class="mr-2{{$offer['owner']['isElite'] ? ' elite' : ''}}">
                    <img class="rounded-circle{{$offer['owner']['isElite'] ? '' : ' border'}}" height="48" width="48"
                         alt="{{$offer['owner']['name']}}"
                         title="{{$offer['owner']['name']}}"
                         src="{{$offer['owner']['imageUrl']}}">
                </div>
                <div class="row">
                    <div class="col-12">
                        <p itemprop="author" class="font-weight-bold mb-0 d-inline">{{$offer['owner']['name']}}</p>
                        <div class="small d-inline font-weight-bold"> - {{$offer['createdAt']}}</div>
                    </div>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-form-label px-2 py-0">
                                {{__('frontend/home/home.offers.type', ['type' => $offer['owner']['businessTypeName'] ?? '-'])}}
                            </div>
                            <div class="col-8">
                                <li class="icon-location3"></li>
                                {{$offer['owner']['country']}} - {{$offer['owner']['city']}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="content">
                @if($offer['salePercentage'])
                    <div
                        summary="{{__('frontend/offer/offer.content.sale_percentage', ['sale_percentage' => $offer['salePercentage']])}}">
                        {{__('frontend/offer/offer.content.sale_percentage', ['sale_percentage' => $offer['salePercentage']])}}
                    </div>
                @endif
                <div>
                    <a href="{{$offer['advertisementUrl']}}"
                       class="card-link">{{__('frontend/offer/offer.content.advertisement_url')}}</a>
                </div>
                <div
                    summary="{{__('frontend/offer/offer.content.rate')}}: {{$offer['rate']}}">
                    {{__('frontend/offer/offer.content.rate')}}: {{$offer['rate']}}
                </div>

                @if($offer['expiresInDays'])
                    @if($offer['isExpired'])
                        @if(in_array($offer['expiresInDays'],[1, 2]))
                            <div
                                summary="{{__('frontend/offer/offer.content.expired_in', ['name' => $offer['expiresInDays'] === 1 ? __('frontend/offer/offer.content.names.day') : __('frontend/offer/offer.content.names.two_days')])}}">
                                {{__('frontend/offer/offer.content.expired_in', ['name' => $offer['expiresInDays'] === 1 ? __('frontend/offer/offer.content.names.day') : __('frontend/offer/offer.content.names.two_days')])}}
                            </div>
                        @elseif(($offer['expiresInDays'] > 2 && $offer['expiresInDays'] < 10))
                            <div
                                summary="{{__('frontend/offer/offer.content.expired_in_number', ['days' => $offer['expiresInDays'], 'name' => __('frontend/offer/offer.content.names.days')])}}">
                                {{__('frontend/offer/offer.content.expired_in_number', ['days' => $offer['expiresInDays'], 'name' => __('frontend/offer/offer.content.names.days')])}}
                            </div>
                        @else
                            <div
                                summary="{{__('frontend/offer/offer.content.expired_in_number', ['days' => $offer['expiresInDays'], 'name' => __('frontend/offer/offer.content.names.day')])}}">
                                {{__('frontend/offer/offer.content.expired_in_number', ['days' => $offer['expiresInDays'], 'name' => __('frontend/offer/offer.content.names.day')])}}
                            </div>
                        @endif
                        <div>
                            {{__('frontend/offer/offer.content.expired_at')}}: {{$offer['expiresAt']}}
                        </div>
                    @else
                        @if(in_array($offer['expiresInDays'],[1, 2]))
                            <div
                                summary="{{__('frontend/offer/offer.content.expires_in', ['name' => $offer['expiresInDays'] === 1 ? __('frontend/offer/offer.content.names.day') : __('frontend/offer/offer.content.names.two_days')])}}">
                                {{__('frontend/offer/offer.content.expires_in', ['name' => $offer['expiresInDays'] === 1 ? __('frontend/offer/offer.content.names.day') : __('frontend/offer/offer.content.names.two_days')])}}
                            </div>
                        @elseif(($offer['expiresInDays'] > 2 && $offer['expiresInDays'] < 10))
                            <div
                                summary="{{__('frontend/offer/offer.content.expires_in_number', ['days' => $offer['expiresInDays'], 'name' => __('frontend/offer/offer.content.names.days')])}}">
                                {{__('frontend/offer/offer.content.expires_in_number', ['days' => $offer['expiresInDays'], 'name' => __('frontend/offer/offer.content.names.days')])}}
                            </div>
                        @else
                            <div
                                summary="{{__('frontend/offer/offer.content.expires_in_number', ['days' => $offer['expiresInDays'], 'name' => __('frontend/offer/offer.content.names.day')])}}">
                                {{__('frontend/offer/offer.content.expires_in_number', ['days' => $offer['expiresInDays'], 'name' => __('frontend/offer/offer.content.names.day')])}}
                            </div>
                        @endif
                        <div>
                            {{__('frontend/offer/offer.content.expires_at')}}: {{$offer['expiresAt']}}
                        </div>
                    @endif
                @endif

                <div>

                    {{__('frontend/offer/offer.content.is_expired')}}
                    :  {{$offer['isExpired'] ? __("frontend/offer/offer.content.boolean.yes") : __("frontend/offer/offer.content.boolean.no")}}
                </div>
            </div>
            <div class="content" style="text-align: initial" dir="auto">
                <div summary="{{Str::limit($offer['content'], 40)}}" class="mb-2">
                    {{$offer['content']}}
                </div>
                @if(count($offer['media']) > 0)
                    <div class="media justify-content-center align-items-center">
                        <div id="animated-thumbnails-gallery" class="text-center" data-id="offer-{{$offer['id']}}">
                            @foreach($offer['media'] as $media)
                                <a href="{{$media['mediaUrl']}}" class="px-2"
                                   {{--data-src="{{$media->getUrl()}}" data-offerer="{{$media->getUrl('thumb')}}"--}} data-thumb="{{$media['thumbnailImageUrl']}}">
                                    <img class="img-fluid py-2 py-sm-0" width="200"
                                         src="{{$media['thumbnailImageUrl']}}"
                                         alt="{{$media['fileName']}}"/>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        let media = document.querySelectorAll('#animated-thumbnails-gallery');
        media.forEach(function (element) {
            let galleryId = element.getAttribute('data-id');
            lightGallery(element, {
                plugins: [lgZoom, lgThumbnail, lgAutoplay, lgFullscreen, lgPager, lgShare, lgVideo],
                thumbnail: true,
                animateThumb: true,
                zoomFromOrigin: true,
                allowMediaOverlap: true,
                toggleThumb: true,
                galleryId: galleryId,
            });
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
                    plugins: [lgZoom, lgThumbnail, lgAutoplay, lgFullscreen, lgPager, lgShare, lgVideo],
                    thumbnail: true,
                    animateThumb: true,
                    zoomFromOrigin: true,
                    allowMediaOverlap: true,
                    toggleThumb: true,
                    galleryId: galleryId,
                });
            })
        });
    </script>
@endpush
