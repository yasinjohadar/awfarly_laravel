<div class="navbar navbar-expand-lg navbar-dark navbar-static">
    <div class="d-flex flex-1 d-lg-none">
        <button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
            <i class="icon-transmission"></i>
        </button>
    </div>

    <div class="navbar-brand text-center text-lg-left">
        <a href="{{url('/')}}" class="d-inline-block">
            <img src="{{Settings::Logo()}}" class="d-none d-sm-block rounded-circle"
                 style="width: 3.125rem; height: 3.125rem; object-fit: cover;" alt="{{Settings::Get('site.name')}}">
            <img src="{{Settings::Logo()}}" class="d-sm-none rounded-circle"
                 style="width: 2.5rem; height: 2.5rem; object-fit: cover;" alt="{{Settings::Get('site.name')}}">
        </a>
    </div>

    <ul class="mr-lg-auto navbar-nav flex-row order-1 order-lg-2 flex-1 flex-lg-0 justify-content-end align-items-center">
        <li class="nav-item nav-item-dropdown-lg dropdown dropdown-user h-100">
            <a href="{{route('home.index')}}" class="navbar-nav-link navbar-nav-link-toggler d-inline-flex align-items-center h-100">
                {{__('auth/navbar.home')}}
            </a>
        </li>
    </ul>
    <ul class="ml-lg-auto navbar-nav flex-row order-1 order-lg-2 flex-1 flex-lg-0 justify-content-end align-items-center">
        @if($user_language)
        <li class="nav-item nav-item-dropdown-lg dropdown">
            <a href="#" class="navbar-nav-link navbar-nav-link-toggler dropdown-toggle"
               dir="ltr" data-toggle="dropdown" aria-expanded="false">
                <img src="{{asset($user_language->image)}}" class="img-flag" alt="{{$user_language->name}}">
                <span class="d-none d-xl-inline-block ml-2 mr-2">{{$user_language->name}}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                @foreach($languages as $language)
                    <a href="{{route('language.change', $language->code)}}"
                       class="dropdown-item{{$user_language->code === $language->code ? ' active': ''}}"
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
