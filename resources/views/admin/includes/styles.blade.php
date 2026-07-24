<!-- Livewire -->
@livewireStyles

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

<!-- animate -->
<link href="{{ asset('assets/css/animate.min.css') }}" rel="stylesheet">

<!-- Livewire DataTables -->
<link href="{{ asset('assets/css/livewire-datatables.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/editors/summernote/css/summernote-bs4.min.css') }}" rel="stylesheet">

<!-- Dual Listbox -->
<link href="{{ asset('assets/plugins/dual-listbox/css/dual-listbox.css') }}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="{{asset('assets/plugins/dual-listbox/css/icon_font/css/icon_font.css')}}"/>

<!-- Color picker -->
<link href="{{ asset('assets/plugins/color-picker/css/nano.min.css') }}" rel="stylesheet">

<!-- Select2 -->
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet">

<!-- Light Gallery -->
<link href="{{ asset('assets/plugins/light-gallery/css/lightgallery-bundle.css') }}" rel="stylesheet">

<!-- Custom -->
<link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">
