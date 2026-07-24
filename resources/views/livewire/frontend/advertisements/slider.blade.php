<div class="swiper-container slider-swiper border shadow-sm">
    <div class="swiper-wrapper">
        @forelse ($slide_advertisements as $advertisement)
            <div class="swiper-slide">
                @if($advertisement->advertisement_url)
                    <a href="{{$advertisement->advertisement_url}}" target="_blank">
                        <img alt="{{$advertisement->getFirstMedia('advertisements')->name}}" height="200"
                             src="{{$advertisement->getFirstMediaUrl('advertisements')}}"/>
                    </a>
                @endif
            </div>
        @empty
            <div class="swiper-slide bg-primary align-items-center d-flex">
                <a class="text-center text-primary-100 display-4 w-100"
                   href="{{route('contact-us.index', 'In-app advertising')}}">
                    {{__('frontend/home/home.content.add-your-ad')}}
                </a>
            </div>
            <div class="swiper-slide bg-secondary align-items-center d-flex">
                <a class="text-center text-secondary-100 display-4 w-100"
                   href="{{route('contact-us.index', 'In-app advertising')}}">
                    {{__('frontend/home/home.content.add-your-ad')}}
                </a>
            </div>
            <div class="swiper-slide bg-indigo align-items-center d-flex">
                <a class="text-center text-indigo-100 display-4 w-100"
                   href="{{route('contact-us.index', 'In-app advertising')}}">
                    {{__('frontend/home/home.content.add-your-ad')}}
                </a>
            </div>
        @endforelse
    </div>
    {{--<div class="swiper-pagination"></div>--}}
    @if($slide_advertisements->count() > 1)
        <div class="swiper-button-next px-sm-5 px-4 font-weight-bold text-dark"></div>
        <div class="swiper-button-prev px-sm-5 px-4 font-weight-bold text-dark"></div>
    @endif
</div>

@push('scripts')
    <script type="text/javascript">
        $(window).bind("load", function() {
            new Swiper(".slider-swiper", {
                spaceBetween: 30,
                effect: "fade",
                loop: true,
                centeredSlides: true,
                grabCursor: false,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                /*pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },*/
            });
        })
    </script>
@endpush
