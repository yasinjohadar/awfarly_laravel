<!doctype html>
<html lang="{{ str_replace('', '-', app()->getLocale()) }}" dir="{{app()->getLocale() === 'ar' ? 'rtl' : 'ltr'}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ Settings::Get('site.name') }}</title>

    @include('admin.includes.styles')
</head>
<body id="app">
@include('admin.includes.navbar')
<div class="page-content">
    @include('admin.includes.sidebar')
    <div class="content-wrapper">
        <div class="content-inner">
            @include('admin.includes.header')
            <div class="content">
                @yield('content')
            </div>
            @include('admin.includes.footer')
        </div>
    </div>
</div>
@auth
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
@endauth

@include('admin.includes.scripts')
</body>
</html>
