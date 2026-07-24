<section id="download_list" class="call-to-action-app section bg-blue">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2>{{__('frontend/home/home.downloads.heading')}}</h2>
                <p>{!! __('frontend/home/home.downloads.description') !!}</p>
                <div class="row align-items-center justify-content-center">
                    @if(Settings::Get('apple.url') != '')
                        <div class="col-md-auto mb-2 mb-md-0">
                            <a href="{{Settings::Get('apple.url')}}" class="btn btn-rounded-icon" target="_blank">
                                <i class="ti-apple"></i>
                                {{__('frontend/home/home.downloads.iphone')}}
                            </a>
                        </div>
                    @endif
                    @if(Settings::Get('android.url') != '')
                        <div class="col-md-auto mb-2 mb-md-0">
                            <a href="{{Settings::Get('android.url')}}" class="btn btn-rounded-icon" target="_blank">
                                <i class="ti-android"></i>
                                {{__('frontend/home/home.downloads.android')}}
                            </a>
                        </div>
                    @endif
                    @if(Settings::Get('huawei.url') != '')
                        <div class="col-md-auto mb-2 mb-md-0" style="direction: initial">
                            <a href="{{Settings::Get('huawei.url')}}" class="btn btn-rounded-icon" target="_blank">
                                <svg style="fill: white" class="d-inline" width="20" height="20" role="img"
                                     viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M3.67 6.14S1.82 7.91 1.72 9.78v.35c.08 1.51 1.22 2.4 1.22 2.4 1.83 1.79 6.26 4.04 7.3 4.55 0 0 .06.03.1-.01l.02-.04v-.04C7.52 10.8 3.67 6.14 3.67 6.14zM9.65 18.6c-.02-.08-.1-.08-.1-.08l-7.38.26c.8 1.43 2.15 2.53 3.56 2.2.96-.25 3.16-1.78 3.88-2.3.06-.05.04-.09.04-.09zm.08-.78C6.49 15.63.21 12.28.21 12.28c-.15.46-.2.9-.21 1.3v.07c0 1.07.4 1.82.4 1.82.8 1.69 2.34 2.2 2.34 2.2.7.3 1.4.31 1.4.31.12.02 4.4 0 5.54 0 .05 0 .08-.05.08-.05v-.06c0-.03-.03-.05-.03-.05zM9.06 3.19a3.42 3.42 0 00-2.57 3.15v.41c.03.6.16 1.05.16 1.05.66 2.9 3.86 7.65 4.55 8.65.05.05.1.03.1.03a.1.1 0 00.06-.1c1.06-10.6-1.11-13.42-1.11-13.42-.32.02-1.19.23-1.19.23zm8.299 2.27s-.49-1.8-2.44-2.28c0 0-.57-.14-1.17-.22 0 0-2.18 2.81-1.12 13.43.01.07.06.08.06.08.07.03.1-.03.1-.03.72-1.03 3.9-5.76 4.55-8.64 0 0 .36-1.4.02-2.34zm-2.92 13.07s-.07 0-.09.05c0 0-.01.07.03.1.7.51 2.85 2 3.88 2.3 0 0 .16.05.43.06h.14c.69-.02 1.9-.37 3-2.26l-7.4-.25zm7.83-8.41c.14-2.06-1.94-3.97-1.94-3.98 0 0-3.85 4.66-6.67 10.8 0 0-.03.08.02.13l.04.01h.06c1.06-.53 5.46-2.77 7.28-4.54 0 0 1.15-.93 1.21-2.42zm1.52 2.14s-6.28 3.37-9.52 5.55c0 0-.05.04-.03.11 0 0 .03.06.07.06 1.16 0 5.56 0 5.67-.02 0 0 .57-.02 1.27-.29 0 0 1.56-.5 2.37-2.27 0 0 .73-1.45.17-3.14z"/>
                                </svg>
                                {{__('frontend/home/home.downloads.huawei')}}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
<footer>
    <div class="footer-main">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-12 m-md-auto align-self-center">
                    <div class="block">
                        <a href="{{url('/')}}">
                            <img src="{{asset('assets/images/frontend/logo_light.png')}}" style="height: 3.125rem;" alt="footer-logo"
                                 class="img-fluid" width="160">
                        </a>
                        <ul class="social-icon list-inline">

                            @if(!empty(Settings::Get('facebook.url')))
                                <li class="list-inline-item">
                                    <a href="{{Settings::Get('facebook.url')}}" target="_blank">
                                        <i class="ti-facebook"></i>
                                    </a>
                                </li>
                            @endif
                            @if(!empty(Settings::Get('twitter.url')))
                                <li class="list-inline-item">
                                    <a href="{{Settings::Get('twitter.url')}}" target="_blank">
                                        <i class="ti-twitter"></i>
                                    </a>
                                </li>
                            @endif
                            @if(!empty(Settings::Get('instagram.url')))
                                <li class="list-inline-item">
                                    <a href="{{Settings::Get('instagram.url')}}" target="_blank">
                                        <i class="ti-instagram"></i>
                                    </a>
                                </li>
                            @endif
                            @if(!empty(Settings::Get('linkedin.url')))
                                <li class="list-inline-item">
                                    <a href="{{Settings::Get('linkedin.url')}}" target="_blank">
                                        <i class="ti-linkedin"></i>
                                    </a>
                                </li>
                            @endif
                            @if(!empty(Settings::Get('youtube.url')))
                                <li class="list-inline-item">
                                    <a href="{{Settings::Get('youtube.url')}}" target="_blank">
                                        <i class="ti-youtube"></i>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-6 mt-5 mt-lg-0">
                    <div class="block-2">
                        <h6>{{__('frontend/home/home.footer.company')}}</h6>
                        <ul>
                            @foreach($pages as $page)
                                <li @if(isset($slug) && $slug === $page['slug']) class='active' @endif>
                                    <a href="{{route('pages.index', ['id' => $page['id'], 'slug' => $page['slug']])}}">
                                        {{$page['title']}}
                                    </a>
                                </li>
                            @endforeach
                            <li @if(Request::routeIs('contact-us.index')) class='active' @endif>
                                <a href="{{route('contact-us.index')}}">
                                    {{__('frontend/home/home.footer.contact')}}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center bg-dark py-4">
        <small
            class="text-secondary h6">{{__('frontend/home/home.footer.copyright', ['now'=>now()->year, 'name' => Settings::Get('site.name')])}}</small>
    </div>
</footer>
<div class="scroll-top-to">
    <i class="ti-angle-up"></i>
</div>
