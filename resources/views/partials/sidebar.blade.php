<div class="vertical-menu">
    <div class="h-100" data-simplebar>
        <div class="navbar-brand-box">
            <a class="logo" href="/">
                <span>
                    <img src="{{ Vite::asset('resources/drezoc/images/logo-light.png') }}" alt="" height="15">
                </span>
                <i>
                    <img src="{{ Vite::asset('resources/drezoc/images/logo-small.png') }}" alt="" height="24">
                </i>
            </a>
        </div>

        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li>
                    <a href="{{ route('dashboard') }}"><i class="feather-home"></i><span>Dashboard</span></a>
                </li>

                <li class="menu-title">Lainnya</li>

                <li>
                    <a href="{{ route('options.index') }}"><i class="feather-settings"></i><span>Konfigurasi</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>
