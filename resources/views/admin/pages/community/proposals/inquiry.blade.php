<div wire:init="loadScripts">
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setProposalId', null)">{{__('pages/community/proposals/inquiry.content.back')}}</button>
        <button title="Edit" @cannot('proposals.edit') disabled
                @endcannot  wire:click="showEditModal({{ $proposal_id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-pencil7"></i>
        </button>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/inquiry.content.proposal_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal_data->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/inquiry.content.advertiser_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal_data->advertiser->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/inquiry.content.advertiser_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal_data->advertiser->name}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/inquiry.content.user_type')}}</div>
                    <div class="col-md-10 font-weight-bold">{{__("pages/community/proposals/index.datatable.users_types.{$proposal_data->user->user_type}")}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/inquiry.content.user_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal_data->user->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/proposals/inquiry.content.user_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$proposal_data->user->name}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/community/proposals/inquiry.content.content')}}</div>
                <div class="text-secondary">
                    {!! $proposal_data->content !!}
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/community/proposals/inquiry.content.answer')}}</div>
                <div class="text-secondary">
                    {!! $proposal_data->answer !!}
                </div>
            </div>
            @if($proposal_data->getMedia('proposals')->count() > 0)
                <div class="col-md-12 mt-5">
                    <div class="font-weight-bold">{{__('pages/community/proposals/inquiry.content.images')}}</div>
                    <ul id="animated-thumbnails-gallery" dir="ltr"
                        class="justified-gallery justify-content-center list-unstyled d-flex">
                        @foreach ($proposal_data->getMedia('proposals') as $index => $media)
                            <li data-id="{{$media->id}}" class="px-3 cursor-pointer position-relative"
                                data-src="{{$media->getUrl()}}">
                                <a wire:click="showDeleteModal({{$media->id}})" class="delete-image text-danger">
                                    <i class="icon-delete-o"></i>
                                </a>
                                <img width="140" class="img-fluid grid-square"
                                     alt="{{$media->name}}"
                                     src="{{$media->getUrl()}}"/>
                                <div
                                    class="font-weight-bold text-center">{{__('pages/community/proposals/inquiry.content.image')}}{{$index+1}}</div>
                            </li>
                            {{--<button class="btn btn-secondary" wire:click=""></button>--}}
                        @endforeach
                    </ul>
                </div>
                <button style="display: none" id="save_order" class="btn btn-secondary"
                        wire:click="setOrder(localStorage.getItem('order').split('|'));">
                    {{__('pages/community/proposals/inquiry.content.save')}}
                </button>
            @endif
        </div>
    </div>
    @if($proposal_data->getMedia('proposals')->count() > 0)
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
    @include('modals.community.proposals.answers.edit')
    @include('modals.community.posts.delete')
</div>
