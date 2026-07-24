<div wire:init="loadScripts">
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setOfferId', null)">{{__('pages/community/offers/show.content.back')}}</button>
        <button title="Edit" @cannot('offers.edit') disabled
                @endcannot  wire:click="showEditModal({{ $offer_id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-pencil7"></i>
        </button>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.user_id')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->advertiser->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.user_name')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->advertiser->name}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.category')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer['category_name'] ?? null}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.sale_percentage')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->sale_percentage ?? '-'}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.advertisement_url')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->advertisement_url ?? '-'}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.expires_at')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->expires_at ?? '-'}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.expires_in')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->expires_in ?? '-'}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.status')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->status}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.rate')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->rate}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.views_count')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->views_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.likes_count')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->likes_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.comments_count')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->comments_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-3">{{__('pages/community/offers/show.content.deleted_at')}}</div>
                    <div class="col-md-9 font-weight-bold">{{$offer->deleted_at ?? '-'}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/community/offers/show.content.content')}}</div>
                <div class="text-secondary">
                    {!! $offer->content !!}
                </div>
            </div>
            @if($offer->getMedia('offers')->count() > 0)
                <div class="col-md-12 mt-5">
                    <div class="font-weight-bold">{{__('pages/community/offers/show.content.media')}}</div>
                    <ul id="animated-thumbnails-gallery" dir="ltr"
                        class="justified-gallery justify-content-center list-unstyled d-flex">
                        @foreach ($offer->getMedia('offers') as $index => $media)
                            <li data-id="{{$media->id}}" data-src="{{$media->getUrl()}}"
                                class="px-3 cursor-pointer position-relative">
                                <a wire:click="showDeleteModal({{$media->id}})" class="delete-image text-danger">
                                    <i class="icon-delete-o"></i>
                                </a>
                                <img data-id="{{$media->id}}" data-src="{{$media->getUrl()}}" data-download-url="{{route('media.download', $media->uuid)}}" data-thumb="{{$media->getUrl()}}" width="140"
                                     class="img-fluid grid-square" alt="{{$media->name}}" src="{{$media->getUrl()}}"/>
                                <div
                                    class="font-weight-bold text-center">{{__('pages/community/offers/show.content.media')}}{{$index+1}}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <button style="display: none" id="save_order" class="btn btn-secondary"
                        wire:click="setOrder(localStorage.getItem('order').split('|'));">
                    {{__('pages/community/offers/show.content.save')}}
                </button>
            @endif
        </div>
    </div>
    @if($offer->getMedia('offers')->count() > 0)
        <script src="{{ asset('assets/plugins/light-gallery/plugins/lg-delete.js') }}"></script>
        <script type="text/javascript">
            const $lgDemoUpdateSlides = document.getElementById(
                'animated-thumbnails-gallery',
            );
            let updateSlideInstance;
            window.addEventListener('loadScripts', () => {

                localStorage.removeItem('order');

                let sortable = new Sortable($lgDemoUpdateSlides, {
                    group: {
                        name: 'images_order',
                        pull: true,
                    },
                    swapThreshold: 1,
                    animation: 150,
                    store: {
                        /**
                         * Get the order of elements. Called once during initialization.
                         * @param   {Sortable}  sortable
                         * @returns {Array}
                         */
                        get: function (sortable) {
                            localStorage.setItem('order', {!! json_encode($order) !!}.join('|'));
                            return {!! json_encode($order) !!};
                        },


                        /**
                         * Save the order of elements. Called onEnd (when the item is dropped).
                         * @param {Sortable}  sortable
                         */
                        set: function (sortable) {
                            let order = sortable.toArray();
                            $('#save_order').show();
                            localStorage.setItem('order', order.join('|'));
                        }
                    }
                });

                updateSlideInstance = lightGallery($lgDemoUpdateSlides, {
                    plugins: [lgZoom, /*lgThumbnail, lgAutoplay, lgComment, lgFullscreen, lgPager,*/ lgRotate/*, lgShare*/, lgVideo],
                    thumbnail: true,
                    animateThumb: true,
                    zoomFromOrigin: true,
                    allowMediaOverlap: true,
                    toggleThumb: true,
                    selector: '.grid-square'
                });
            });
            //add event listener to refresh file input
            window.addEventListener('clearFileInput', (event) => {
                $('#media').val(null);
                let order = event.detail;
                let sortable = new Sortable($lgDemoUpdateSlides, {
                    group: {
                        name: 'images_order',
                        pull: true,
                    },
                    swapThreshold: 1,
                    animation: 150,
                    store: {
                        /**
                         * Get the order of elements. Called once during initialization.
                         * @param   {Sortable}  sortable
                         * @returns {Array}
                         */
                        get: function (sortable) {
                            localStorage.setItem('order', order.join('|'));
                            return order;
                        },


                        /**
                         * Save the order of elements. Called onEnd (when the item is dropped).
                         * @param {Sortable}  sortable
                         */
                        set: function (sortable) {
                            let order = sortable.toArray();
                            $('#save_order').show();
                            localStorage.setItem('order', order.join('|'));
                        }
                    }
                });
            });

            window.addEventListener('resetLightGallery', (event) => {
                /*updateSlideInstance.destroy()*/

                let $container = document.querySelector('.lg-container');
                if ($container) {
                    $container.remove();
                }
                if (localStorage.getItem('order').split('|').length > 0) {
                    updateSlideInstance = lightGallery($lgDemoUpdateSlides, {
                        plugins: [lgZoom, /*lgThumbnail, lgAutoplay, lgComment, lgFullscreen, lgPager,*/ lgRotate/*, lgShare*/, lgVideo],
                        thumbnail: true,
                        animateThumb: true,
                        zoomFromOrigin: true,
                        allowMediaOverlap: true,
                        toggleThumb: true,
                        selector: '.grid-square',
                        galleryId: "gallery2",
                    });
                }
            });
        </script>
    @endif
    @include('modals.community.offers.editMedia')
    @include('modals.community.offers.delete')
</div>
