<div wire:init="loadScripts">
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setAdvertisementId', null)">{{__('pages/advertisements/side/show.content.back')}}</button>
        <button title="Edit" @cannot('advertisements.edit') disabled
                @endcannot  wire:click="showEditModal({{ $advertisement_id }})"
                class="btn btn-secondary mx-1">
            <i class="icon-pencil7"></i>
        </button>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/side/show.content.url')}}</div>
                    <div class="col-md-10 font-weight-bold">{{$advertisement->advertisement_url}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/side/show.content.side.title')}}</div>
                    <div class="col-md-10 font-weight-bold">{{__("pages/advertisements/side/show.content.side.{$advertisement->side}")}}</div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/side/show.content.starts_at')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$advertisement['starts_at']}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/side/show.content.ends_at')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$advertisement['ends_at']}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">{{__('pages/advertisements/side/show.content.is_expired')}}</div>
                    <div class="col-md-10 font-weight-bold">
                        {{$advertisement->is_expired ? __("pages/advertisements/side/show.content.boolean.yes") : __("pages/advertisements/side/show.content.boolean.no")}}
                    </div>
                </div>
            </div>
            @if($advertisement->getMedia('advertisements')->count() > 0)
                <div class="col-md-12 mt-5">
                    <div class="font-weight-bold">{{__('pages/advertisements/side/show.content.image')}}</div>
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
                                    class="font-weight-bold text-center">{{__('pages/advertisements/side/show.content.image')}}{{$index+1}}</div>
                            </li>
                            {{--<button class="btn btn-secondary" wire:click=""></button>--}}
                        @endforeach
                    </ul>
                </div>
                <button style="display: none" id="save_order" class="btn btn-secondary"
                        wire:click="setOrder(localStorage.getItem('order').split('|'));">
                    {{__('pages/advertisements/side/show.content.save')}}
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
                $('#image').val(null);
            });

            window.addEventListener('resetLightGallery', (event) => {
                /*updateSlideInstance.destroy()*/
                let $container = document.querySelector('.lg-container');
                if ($container) {
                    $container.remove();
                }
                @if($advertisement->getMedia('advertisements')->count() > 0)
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
                @endif
            });
        </script>
    @endif
    @include('modals.advertisements.side.edit')
    @include('modals.advertisements.delete')
</div>
