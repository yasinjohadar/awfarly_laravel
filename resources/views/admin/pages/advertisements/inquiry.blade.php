<div wire:init="loadScripts">
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setAdvertisementId', null)">{{__('pages/advertisements/show.content.back')}}</button>
        <button title="Edit" @cannot('advertisements.edit') disabled
                @endcannot  wire:click="showEditModal({{ $advertisement_id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-pencil7"></i>
        </button>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/show.content.name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$advertisement->advertiser_name}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/show.content.url')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$advertisement->advertiser_url}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/show.content.type')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{__("pages/advertisements/show.content.type_values.$advertisement->type")}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/show.content.users')}}</div>
                    <div
                        class="col-md-10 font-weight-bold">{{__("pages/advertisements/show.content.users_values.$advertisement->users")}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/show.content.starts_at')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$advertisement['starts_at']}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/show.content.ends_at')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$advertisement['ends_at']}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/show.content.is_active')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$advertisement->is_active ? __("pages/advertisements/show.content.boolean.yes") : __("pages/advertisements/show.content.boolean.no")}}
                    </div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/advertisements/show.content.categories')}}</div>
                <div class="text-secondary">
                    {{$categories}}
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/advertisements/show.content.governorates')}}</div>
                <div class="text-secondary">
                    {{$governorates}}
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/advertisements/show.content.cities')}}</div>
                <div class="text-secondary">
                    {{$cities}}
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/advertisements/show.content.content')}}</div>
                <div class="text-secondary">
                    {!! $advertisement->content !!}
                </div>
            </div>
            @if($advertisement->getMedia('advertisements')->count() > 0)
                <div class="col-md-12 mt-5">
                    <div class="font-weight-bold">{{__('pages/advertisements/show.content.files')}}</div>
                    <ul id="animated-thumbnails-gallery" dir="ltr"
                        class="justified-gallery justify-content-center list-unstyled d-flex">
                        @foreach ($advertisement->getMedia('advertisements') as $index => $media)
                            <li data-id="{{$media->id}}" data-src="{{$media->getUrl()}}"
                                class="px-3 cursor-pointer position-relative">
                                <a wire:click="showDeleteModal({{$media->id}})" class="delete-image text-danger">
                                    <i class="icon-delete-o"></i>
                                </a>
                                <img data-id="{{$media->id}}" data-src="{{$media->getUrl()}}" data-download-url="{{route('media.download', $media->uuid)}}"
                                     data-thumb="{{$media->getUrl()}}" width="140"
                                     class="img-fluid grid-square" alt="{{$media->name}}" src="{{$media->getUrl()}}"/>
                                <div
                                    class="font-weight-bold text-center">{{__('pages/advertisements/show.content.file')}}{{$index+1}}</div>
                            </li>
                            {{--<button class="btn btn-secondary" wire:click=""></button>--}}
                        @endforeach
                    </ul>
                </div>
                <button style="display: none" id="save_order" class="btn btn-secondary"
                        wire:click="setOrder(localStorage.getItem('order').split('|'));">
                    {{__('pages/advertisements/show.content.save')}}
                </button>
            @endif
        </div>
    </div>
    @if($advertisement->getMedia('advertisements')->count() > 0)
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
    @include('modals.advertisements.addImages')
    @include('modals.advertisements.delete')
</div>
