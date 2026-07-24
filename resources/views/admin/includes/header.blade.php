<!-- Page header -->
<div class="page-header page-header-light">
    <div class="page-header-content container-fluid d-sm-flex">
        <div class="page-title">
            <h4>@yield('title')</h4>
        </div>
    </div>

    <div class="breadcrumb-line breadcrumb-line-light px-0">
        <div class="page-header-content container-fluid header-elements-sm-inline">
            <div class="d-flex">
                <div class="breadcrumb">
                    <a href="{{url('/')}}" class="breadcrumb-item">
                        <i class="icon-home2 mr-2"></i>
                        {{__('breadcrumb.home')}}
                    </a>
                    @yield('breadcrumbs')
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page header -->
