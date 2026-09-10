<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a href="{{route('admin.system.whatsapp.settings')}}"
           class="nav-link{{Request::routeIs('admin.system.whatsapp.settings') ? ' active' : ''}}">
            {{__('pages/system/whatsapp.nav.settings')}}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{route('admin.system.whatsapp.instances')}}"
           class="nav-link{{Request::routeIs('admin.system.whatsapp.instances') ? ' active' : ''}}">
            {{__('pages/system/whatsapp.nav.instances')}}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{route('admin.system.whatsapp.send')}}"
           class="nav-link{{Request::routeIs('admin.system.whatsapp.send') ? ' active' : ''}}">
            {{__('pages/system/whatsapp.nav.send')}}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{route('admin.system.whatsapp.groups')}}"
           class="nav-link{{Request::routeIs('admin.system.whatsapp.groups') ? ' active' : ''}}">
            {{__('pages/system/whatsapp.nav.groups')}}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{route('admin.system.whatsapp.templates')}}"
           class="nav-link{{Request::routeIs('admin.system.whatsapp.templates') ? ' active' : ''}}">
            {{__('pages/system/whatsapp.nav.templates')}}
        </a>
    </li>
</ul>
