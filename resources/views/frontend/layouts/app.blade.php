<!doctype html>
<html amp class="custom-scrollbars" lang="{{ str_replace('', '-', app()->getLocale()) }}"
      dir="{{app()->getLocale() === 'ar' ? 'rtl' : 'ltr'}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{Settings::Get('site.name')}}</title>
    <meta name="description" content="@yield('meta-description', Settings::Get('site.description'))">
    <meta name="keywords" content="@yield('meta-keywords', Settings::Get('site.keywords'))">
    <meta name="author" content="@yield('meta-title', Settings::Get('site.name'))">
    <meta property="og:title" content="@yield('meta-title', Settings::Get('site.name'))"/>
    <meta property="og:type" content="@yield('meta-type', 'website')"/>
    <meta property="og:url" content="@yield('meta-url', url('/'))"/>
    <meta property="og:image" itemprop="image"
          content="@yield('meta-image', Settings::Logo('assets/images/frontend/logo.png'))"/>
    <meta property="og:locale" content="{{ str_replace('', '-', app()->getLocale()) }}"/>
    <meta property="og:site_name" content="{{Settings::Get('site.name')}}"/>
    <meta property="og:description" content="@yield('meta-description', Settings::Get('site.description'))"/>
    <meta name="twitter:card" content="@yield('meta-type', 'website')"/>
    <meta name="twitter:title" content="@yield('meta-title', Settings::Get('site.name'))"/>
    <meta name="twitter:image" content="@yield('meta-image', Settings::Logo('assets/images/frontend/logo.png'))"/>
    <meta name="twitter:description" content="@yield('meta-description', Settings::Get('site.description'))"/>

    <!-- Start SmartBanner configuration -->
    <meta name="google-play-app" content="app-id=com.m3rady.app">
    <meta name="apple-itunes-app" content="app-id=1573227010">
    <meta name="smartbanner:title" content="{{Settings::Get('site.name')}}">
    <meta name="smartbanner:author" content="{{Settings::Get('site.name')}}">
    <meta name="smartbanner:price" content="FREE">
    <meta name="smartbanner:price-suffix-apple" content=" - On the App Store">
    <meta name="smartbanner:price-suffix-google" content=" - In Google Play">
    <meta name="smartbanner:icon-apple" content="{{asset('assets/images/appstore.png')}}">
    <meta name="smartbanner:icon-google" content="{{asset('assets/images/playstore.png')}}">
    <meta name="smartbanner:button" content="VIEW">
    <meta name="smartbanner:button-url-apple" content="{{Settings::Get('apple.url')}}">
    <meta name="smartbanner:button-url-google" content="{{Settings::Get('android.url')}}">
    <meta name="smartbanner:enabled-platforms" content="android,ios">
    <meta name="smartbanner:close-label" content="Close">
    <!-- End SmartBanner configuration -->
    <!-- Global site tag (gtag.js) - Google Analytics -->
    @include('frontend.includes.styles')
    <style>
        @media (max-width: 480px){
            #animated-thumbnails-gallery img {
                 width: revert-layer !important;
            }
        }
    </style>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-210602622-1"></script>
    <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'UA-210602622-1'); </script>
</head>
<body id="app" class="body-wrapper" data-spy="scroll" data-target=".privacy-nav">
@include('frontend.includes.header')
<section class="section pt-0 position-relative">
    <div class="content">
        <div class="row">
            @if(Request::routeIs('home.index') && !Settings::Get('maintenance.mode', 0))
                <div class="col-lg-12">
                    @livewire('frontend.categories.categories-component')
                </div>
            @endif
            <div class="col-lg-2 d-none d-lg-block">
                @livewire('frontend.advertisements.left-side-advertisements-component')
            </div>
            <div class="col-lg-8">
                @if(Request::routeIs('home.index') && Settings::Get('maintenance.mode', 0))
                    <div class="card">
                        <div class="card-header">
                            {{__('frontend/home/home.maintenance.title')}}
                        </div>
                        <div class="card-body">
                            <div class="h1">
                                {{__('frontend/home/home.maintenance.content')}}
                            </div>
                        </div>
                    </div>
                @else
                    @yield('content')
                @endif
            </div>
            <div class="col-lg-2 d-none d-lg-block">
                @livewire('frontend.advertisements.right-side-advertisements-component')
            </div>
        </div>
    </div>
</section>
@include('frontend.includes.footer')
@include('frontend.includes.scripts')
</body>
</html>
