<div class="sidebar-section sidebar-user my-1">
    <div class="sidebar-section-body">
        <div class="media">
            <a href="#" class="mr-3">
                <img src="{{route('user.profile.image')}}" class="rounded-circle" alt="{{Auth::guard('admin')->user()->name}}">
            </a>

            <div class="media-body">
                <div class="font-weight-semibold">{{Auth::user()->name}}</div>
                <div class="font-size-sm line-height-sm opacity-50">
                    {{$user_role ?? '-'}}
                </div>
            </div>

            <div class="ml-3 align-self-center">
                <button type="button"
                        class="btn btn-outline-light-100 text-white border-transparent btn-icon rounded-pill btn-sm sidebar-control sidebar-main-resize d-none d-lg-inline-flex">
                    <i class="icon-transmission"></i>
                </button>

                <button type="button"
                        class="btn btn-outline-light-100 text-white border-transparent btn-icon rounded-pill btn-sm sidebar-mobile-main-toggle d-lg-none">
                    <i class="icon-cross2"></i>
                </button>
            </div>
        </div>
    </div>
</div>
