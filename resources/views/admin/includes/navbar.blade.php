<div class="navbar navbar-expand-lg navbar-dark navbar-static">
    <div class="d-flex flex-1 d-lg-none">
        <button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
            <i class="icon-transmission"></i>
        </button>
    </div>

    <div class="navbar-brand text-center text-lg-left d-flex align-items-center">
        <button type="button"
                class="btn btn-outline-light-100 text-white border-transparent btn-icon rounded-pill btn-sm sidebar-control sidebar-main-resize d-none d-lg-inline-flex mr-2">
            <i class="icon-transmission"></i>
        </button>
        <a href="{{url('/')}}" class="d-inline-block">
            <img src="{{asset('assets/images/logo_light.png')}}" class="d-none d-sm-block" style="height: 3.125rem;" alt="">
            <img src="{{asset('assets/images/logo_icon_light.png')}}" class="d-sm-none" alt="">
        </a>
    </div>

    <ul class="ml-lg-auto navbar-nav flex-row order-1 order-lg-2 flex-1 flex-lg-0 justify-content-end align-items-center">
        <li class="nav-item nav-item-dropdown-xl dropdown">
            <a href="#" class="navbar-nav-link navbar-nav-link-toggler dropdown-toggle"
               dir="ltr" data-toggle="dropdown" aria-expanded="false">
                <img src="{{asset($user_language->image)}}" class="img-flag" alt="{{$user_language->name}}">
                <span class="d-none d-xl-inline-block ml-2 mr-2">{{$user_language->name}}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                @foreach($languages as $language)
                    <a href="{{route('admin.language.change', $language->code)}}"
                       class="dropdown-item{{$user_language->code === $language->code ? ' active': ''}}"
                       dir="ltr">
                        <img src="{{asset($language->image)}}" class="img-flag"
                             alt="{{$language->name}}">
                        {{$language->name}}
                    </a>
                @endforeach
            </div>
        </li>
        <li class="nav-item nav-item-dropdown-lg dropdown dropdown-user h-100">
            <a href="#"
               class="navbar-nav-link navbar-nav-link-toggler dropdown-toggle d-inline-flex align-items-center h-100{{(Request::routeIs('admin.account.edit')) ? ' active' : ''}}"
               data-toggle="dropdown">
                <img src="{{route('user.profile.image')}}" class="rounded-circle"
                     alt="{{Auth::guard('admin')->user()->name}}" width="34" height="34">
                <span class="d-none d-lg-inline-block ml-2">{{ Auth::user()->name }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-right">
                <h6 class="dropdown-header">
                    {{__('navbar.account.login_at', ['datetime'=> Auth::guard('admin')->user()->last_login_at ? \Carbon\Carbon::make(Auth::guard('admin')->user()->last_login_at)->diffForHumans() : '-'])}}
                </h6>
                <div class="dropdown-divider"></div>
                <a href="{{route('admin.account.edit')}}" class="dropdown-item{{(Request::routeIs('admin.account.edit')) ? ' active' : ''}}">
                    <i class="icon-cog5"></i>
                    {{__('navbar.account.settings')}}
                </a>
                <a href="{{ route('logout') }}" class="dropdown-item"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="icon-switch2"></i>
                    {{__('navbar.account.logout')}}
                </a>
            </div>
        </li>
    </ul>
</div>
