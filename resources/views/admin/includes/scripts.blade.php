<!-- Livewire -->
@livewireScripts

<!-- Scripts -->
<script src="{{ asset('assets/js/app.js') }}"></script>

<!-- Limitless theme -->
<script src="{{ asset('assets/js/limitless.js') }}"></script>

<!-- Select2 -->
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

<!-- CKEditor -->
<script src="{{ asset('assets/plugins/editors/ckeditor/ckeditor.js') }}"></script>
{{--<script src="{{ asset('assets/plugins/editors/summernote/js/summernote.min.js') }}"></script>--}}
<!-- ECharts -->
<script src="{{ asset('assets/plugins/visualization/echarts/echarts.min.js') }}"></script>
<script src="{{ asset('assets/plugins/maps/echarts/world.js') }}"></script>

<!-- toastr -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<x-livewire-alert::scripts/>

<!-- Dual Listbox -->
<script src="{{ asset('assets/plugins/dual-listbox/js/dual-listbox.js') }}"></script>

<!-- Color picker -->
<script src="{{ asset('assets/plugins/color-picker/js/pickr.min.js') }}"></script>

<!-- Light Gallery -->
<script src="{{ asset('assets/plugins/light-gallery/lightgallery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/autoplay/lg-autoplay.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/comment/lg-comment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/fullscreen/lg-fullscreen.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/hash/lg-hash.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/mediumZoom/lg-medium-zoom.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/pager/lg-pager.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/relativeCaption/lg-relative-caption.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/rotate/lg-rotate.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/share/lg-share.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/thumbnail/lg-thumbnail.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/video/lg-video.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/zoom/lg-zoom.min.js') }}"></script>
<script src="{{ asset('assets/plugins/light-gallery/plugins/lg-delete.js') }}"></script>

<!-- Sortable -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<!-- Vector Map -->
<script src="{{asset('assets/plugins/maps/jvectormap/jvectormap.min.js')}}"></script>
<script src="{{asset('assets/plugins/maps/jvectormap/map_files/world.js')}}"></script>
<script src="{{asset('assets/plugins/maps/jvectormap/map_files/countries/usa.js')}}"></script>
<script src="{{asset('assets/plugins/maps/jvectormap/map_files/countries/germany.js')}}"></script>


<!-- Custom -->
<script src="{{ asset('assets/js/custom.js') }}"></script>
<!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/8.9.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.8.1/firebase-firestore.js"></script>

<script>
    /* Firebase config */
    var firebaseConfig = {
        apiKey: "AIzaSyCA3OHEO8pQBO56UgGxmEsx5ewscHjxvdI",
        authDomain: "pc-api-4652760247159134803-62.firebaseapp.com",
        projectId: "pc-api-4652760247159134803-62",
        storageBucket: "pc-api-4652760247159134803-62.appspot.com",
        messagingSenderId: "185661664972",
        appId: "1:185661664972:web:47a9a505a1a783c1835c69",
        measurementId: "G-RJXEQN9HJZ"
    };

    firebase.initializeApp(firebaseConfig);
</script>

@stack('scripts')
