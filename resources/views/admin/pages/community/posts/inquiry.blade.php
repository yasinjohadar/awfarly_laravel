<div wire:init="loadScripts">
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setPostId', null)">{{__('pages/community/posts/inquiry.content.back')}}</button>
        <button title="Edit" @cannot('posts.edit') disabled
                @endcannot  wire:click="showEditModal({{ $post_id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-pencil7"></i>
        </button>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/inquiry.content.category')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->category_name}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/inquiry.content.post_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/inquiry.content.user_type')}}</div>
                    <div class="col-md-10 font-weight-bold">{{ucwords($post->user->user_type)}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/inquiry.content.user_id')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->user->id}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/inquiry.content.user_name')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->user->name}}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/inquiry.content.views_count')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->views_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/inquiry.content.likes_count')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->likes_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/inquiry.content.comments_count')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->comments_count}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/community/posts/inquiry.content.deleted_at')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$post->deleted_at ?? '-'}}</div>
                </div>
            </div>
            <div class="col-md-12 mt-5">
                <div class="font-weight-bold">{{__('pages/community/posts/inquiry.content.content')}}</div>
                <div class="text-secondary">
                    {!! $post->content !!}
                </div>
            </div>
            @if($post->getMedia('posts')->count() > 0)
                <div class="col-md-12 mt-5">
                    <div class="font-weight-bold">{{__('pages/community/posts/inquiry.content.images')}}</div>
                    <ul id="animated-thumbnails-gallery" dir="ltr"
                        class="justified-gallery justify-content-center list-unstyled d-flex">
                        @foreach ($post->getMedia('posts') as $index => $media)
                            @php($media_url = \App\Helpers\Files::mediaUrl($media))
                            <li data-id="{{$media->id}}" data-src="{{$media_url}}"
                                class="px-3 cursor-pointer position-relative">
                                <a wire:click="showDeleteModal({{$media->id}})" class="delete-image text-danger">
                                    <i class="icon-delete-o"></i>
                                </a>
                                <img data-id="{{$media->id}}" data-src="{{$media_url}}"
                                     data-download-url="{{route('media.download', $media->uuid)}}"
                                     data-thumb="{{$media_url}}" width="140"
                                     class="img-fluid grid-square" alt="{{$media->name}}" src="{{$media_url}}"/>
                                <div
                                    class="font-weight-bold text-center">{{__('pages/community/posts/inquiry.content.image')}}{{$index+1}}</div>
                            </li>
                            {{--<button class="btn btn-secondary" wire:click=""></button>--}}
                        @endforeach
                    </ul>
                </div>
                <button style="display: none" id="save_order" class="btn btn-secondary"
                        wire:click="setOrder(localStorage.getItem('order').split('|'));">
                    {{__('pages/community/posts/inquiry.content.save')}}
                </button>
            @endif
        </div>
    </div>
    @if($post->getMedia('posts')->count() > 0)
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
    @include('modals.community.posts.addImages')
    @include('modals.community.posts.delete')
</div>
