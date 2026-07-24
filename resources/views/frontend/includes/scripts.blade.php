<!-- Livewire -->
@livewireScripts

<!-- Scripts -->
<script src="{{ asset('assets/js/app.js') }}"></script>

<!-- Limitless theme -->
<script src="{{ asset('assets/js/limitless.js') }}"></script>

<!-- Light Gallery -->
<script src="{{ asset('assets/plugins/light-gallery/lightgallery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/autoplay/lg-autoplay.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/fullscreen/lg-fullscreen.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/hash/lg-hash.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/mediumZoom/lg-medium-zoom.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/pager/lg-pager.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/share/lg-share.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/thumbnail/lg-thumbnail.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/video/lg-video.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/zoom/lg-zoom.min.js') }}"></script>

<!-- Swiper JS -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

<!-- Plugins -->
<script src="{{ asset('assets/plugins/slick/slick.min.js') }}"></script>
<script src="{{ asset('assets/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
<script src="{{ asset('assets/plugins/syotimer/jquery.syotimer.min.js') }}"></script>
<script src="{{ asset('assets/plugins/aos/aos.js') }}"></script>
<script src="{{ url('https://cdnjs.cloudflare.com/ajax/libs/jquery.smartbanner/1.0.0/jquery.smartbanner.min.js') }}"></script>

<!-- Theme-->
<script src="{{asset('assets/js/frontend/script.js')}}"></script>

<!-- toastr -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<x-livewire-alert::scripts/>

<!-- Custom -->
<script src="{{ asset('assets/js/custom.js') }}"></script>

@stack('scripts')
