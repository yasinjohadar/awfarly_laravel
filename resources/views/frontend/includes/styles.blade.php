<!-- Livewire -->
@livewireStyles

<!-- Favicon -->
<link rel="shortcut icon" type="image/x-icon" href="{{asset('favicon.ico')}}"/>

<!-- Fonts -->
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
<link href='https://fonts.googleapis.com/css?family=Cairo' rel='stylesheet'>

@if (app()->getLocale() === 'ar')
    <!-- Limitless theme -->
    <link href="{{asset('assets/css/rtl/limitless.min.css') }}" rel="stylesheet">
@else
    <!-- Limitless theme -->
    <link href="{{asset('assets/css/ltr/limitless.min.css')}}" rel="stylesheet">
@endif

<!-- Icomon -->
<link href="{{ asset('assets/css/icons/icomoon/styles.min.css') }}" rel="stylesheet">

<!-- Light Gallery -->
<link href="{{ asset('assets/plugins/light-gallery/css/lightgallery-bundle.css') }}" rel="stylesheet">

<!-- Swiper JS -->
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>

<!-- Plugins -->
<link href="{{ asset('assets/plugins/themify-icons/themify-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/slick/slick.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/slick/slick-theme.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/fancybox/jquery.fancybox.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/aos/aos.css') }}" rel="stylesheet">
<link href="{{ url('https://cdnjs.cloudflare.com/ajax/libs/jquery.smartbanner/1.0.0/jquery.smartbanner.css') }}" rel="stylesheet">
<!-- Plugins -->

<!-- Custom -->
<link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">

<!-- Theme -->
<link href="{{ asset('assets/css/frontend/style.css') }}" rel="stylesheet">
