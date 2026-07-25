<!-- Page header -->
<nav id="navbar" class="navbar main-nav navbar-expand-lg px-2 px-sm-0 py-2 py-lg-0 fixed-top shadow-sm">
    <div class="container justify-content-center">
        <div class="d-flex justify-content-around">
            <a class="navbar-brand py-2 w-auto" href="{{url('/')}}">
                <img class="img-fluid" width="36" style="height: auto !important;"
                     src="{{asset('assets/images/frontend/logo.png')}}" alt="logo">
            </a>
            <div class="d-flex align-items-center">
                <a class="py-2 mx-2 mx-md-3" href="{{Settings::Get('android.url')}}">
                    <img class="img-fluid" width="100" style="height: auto !important;"
                         src="{{asset('assets/images/frontend/stores/googleplay.png')}}" alt="logo">
                </a>
                <a class="py-2 mx-2 mx-md-3" href="{{Settings::Get('apple.url')}}">
                    <img class="img-fluid" width="110" style="height: auto !important;"
                         src="{{asset('assets/images/frontend/stores/appstore.png')}}" alt="logo">
                </a>
            </div>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="ti-menu"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li id="home" class="nav-item{{Request::routeIs('home.index') ? ' active' : ''}}">
                    <a class="nav-link" href="{{url('/')}}">
                        {{__('frontend/navbar.home')}}
                    </a>
                </li>
                <li id="download-li" class="nav-item cursor-pointer">
                    <a id="download" class="nav-link">
                        {{__('frontend/navbar.download')}}
                    </a>
                </li>
                @if($about_us)
                <li id="about-us" class="nav-item{{isset($slug) && $slug === $about_us['slug'] ? ' active' : ''}}">
                    <a class="nav-link"
                       href="{{route('pages.index', ['id' => $about_us['id'], 'slug' => $about_us['slug']])}}">
                        {{$about_us['title']}}
                    </a>
                </li>
                @endif
                <li id="contact-us" class="nav-item{{Request::routeIs('contact-us.index') ? ' active' : ''}}">
                    <a class="nav-link" href="{{route('contact-us.index')}}">
                        {{__('frontend/navbar.contact-us')}}
                    </a>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('admin.dashboard')}}">
                            {{__('frontend/navbar.admin-panel')}}
                        </a>
                    </li>
                @endauth
                @if($user_language)
                <li class="nav-item dropdown" id="languages_select">
                    <a href="#" class="nav-link dropdown-toggle d-flex justify-content-center" id="languages"
                       dir="ltr" data-toggle="dropdown" aria-expanded="false">
                        <img src="{{asset($user_language->image)}}" class="img-flag my-auto"
                             alt="{{$user_language->name}}">
                        <span class="d-inline-block ml-2 mr-2" id="selected_language">{{$user_language->name}}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right m-0 border-2 border-md-0">
                        @foreach($languages as $language)
                            <a href="{{route('language.change', $language->code)}}"
                               class="dropdown-item justify-content-center{{$user_language->code === $language->code ? ' active': ''}}"
                               dir="ltr">
                                <img src="{{asset($language->image)}}" class="img-flag"
                                     alt="{{$language->name}}">
                                {{$language->name}}
                            </a>
                        @endforeach
                    </div>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

{{--<section class="section gradient-banner">
    <div class="shapes-container">
        <div class="shape aos-init aos-animate" data-aos="fade-down-left" data-aos-duration="1500"
             data-aos-delay="100"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-down" data-aos-duration="1000"
             data-aos-delay="100"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-up-right" data-aos-duration="1000"
             data-aos-delay="200"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-down-left" data-aos-duration="1000"
             data-aos-delay="100"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-down-left" data-aos-duration="1000"
             data-aos-delay="100"></div>
        <div class="shape aos-init aos-animate" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="300"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-down-right" data-aos-duration="500"
             data-aos-delay="200"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-down-right" data-aos-duration="500"
             data-aos-delay="100"></div>
        <div class="shape aos-init aos-animate" data-aos="zoom-out" data-aos-duration="2000" data-aos-delay="500"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-up-right" data-aos-duration="500"
             data-aos-delay="200"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-down-left" data-aos-duration="500"
             data-aos-delay="100"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-up" data-aos-duration="500" data-aos-delay="0"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-down" data-aos-duration="500" data-aos-delay="0"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-up-right" data-aos-duration="500"
             data-aos-delay="100"></div>
        <div class="shape aos-init aos-animate" data-aos="fade-down-left" data-aos-duration="500"
             data-aos-delay="0"></div>
    </div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 order-2 order-md-1 text-center text-md-left">
                <h1 class="text-white font-weight-bold mb-4">{{__('frontend/home/home.content.heading')}}</h1>
                <p class="text-white mb-5">{{__('frontend/home/home.content.description', ['app_name'=>Settings::Get('site.name')])}}</p>
                <a id="download" class="btn btn-main-md">{{__('frontend/home/home.content.download')}}</a>
            </div>
            <div class="col-md-6 text-center order-1 order-md-2">
                <img class="img-fluid" src="{{asset('assets/images/frontend/mobile.png')}}" alt="screenshot">
            </div>
        </div>
    </div>
</section>--}}
<div class="position-relative pull-top">
    <div class="container">
        <div class="rounded shadow p-2 bg-white">
            @livewire('frontend.advertisements.slider-advertisements-component')
        </div>
    </div>
</div>
<!-- /page header -->
@push('scripts')
    <!-- Initialize Swiper -->
    <script>
        $(document).ready(function () {
            //add event listener to the link element
            let downloadElements = document.querySelectorAll('#download');
            downloadElements.forEach(function (LinkElement) {
                LinkElement.addEventListener('click', function () {
                    let sectionTop = document.getElementById('download_list').offsetTop;
                    window.scrollTo({
                        top: sectionTop - 90,
                        behavior: 'smooth'
                    });
                })
                //window listener
                window.addEventListener('scroll', function () {
                    const section = document.getElementById('download_list');

                    //get bounding client rectangle for the section
                    const sectionRect = section.getBoundingClientRect();

                    //get section link element by its id
                    const sectionLink = document.getElementById("download");
                    //check if the current section rectangle is within specific region.
                    if (sectionRect.top >= -(sectionRect.height / 2) && sectionRect.top <= (sectionRect.height / 2)) {
                        //set the section link to active to mark it
                        sectionLink.parentElement.classList.add('active');
                        @if(Request::routeIs('home.index'))
                        document.getElementById('home').classList.remove('active');
                        @elseif($about_us && isset($slug) && $slug === $about_us['slug'])
                        document.getElementById('about-us').classList.remove('active');
                        @elseif (Request::routeIs('contact-us.index'))
                        document.getElementById('contact-us').classList.remove('active');
                        @endif
                    } else {
                        //remove the section link active class to unmark it
                        sectionLink.parentElement.classList.remove('active');
                        @if(Request::routeIs('home.index'))
                        document.getElementById('home').classList.add('active');
                        @elseif($about_us && isset($slug) && $slug === $about_us['slug'])
                        document.getElementById('about-us').classList.add('active');
                        @elseif (Request::routeIs('contact-us.index'))
                        document.getElementById('contact-us').classList.add('active');
                        @endif
                    }
                });
            })

        });
    </script>
@endpush
