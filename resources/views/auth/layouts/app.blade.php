<!doctype html>
<html lang="{{ str_replace('', '-', app()->getLocale()) }}" dir="{{app()->getLocale() === 'ar' ? 'rtl' : 'ltr'}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ config('app.name', Settings::Get('site.name')) }}</title>

    @include('auth.includes.styles')
</head>
<body id="app">
@include('auth.includes.navbar')
<div class="page-content">
    <div class="content-wrapper">
        <div class="content-inner">
            <div class="content">
                @yield('content')
            </div>
        </div>
        {{-- @include('auth.includes.footer') --}}
    </div>
</div>
@include('auth.includes.scripts')
</body>
</html>
