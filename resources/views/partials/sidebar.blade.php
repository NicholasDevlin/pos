<div class="vertical-menu">
    <div class="h-100" data-simplebar>
        <div class="navbar-brand-box mt-3">
            <a class="logo" href="/">
                <span>
                    <img src="{{ asset('assets/images/logo.png') }}" alt="" height="30">
                </span>
                <i>
                    <img src="{{ asset('assets/images/logo.png') }}" alt="" height="48">
                </i>
            </a>
        </div>

        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li><a href="{{ route('dashboard') }}"><i class="feather-home"></i><span>Dashboard</span></a></li>

                @role('super-admin')
                    <li class="menu-title">Manajemen Pengguna</li>

                    <li>
                        <a class="has-arrow" href="javascript: void(0);"><i class="feather-user-plus"></i><span>Akses Pengguna</span></a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('locations.index') }}">Lokasi</a></li>
                            <li><a href="{{ route('business-units.index') }}">Unit Bisnis</a></li>
                            <li><a href="{{ route('permissions.index') }}">Hak Akses</a></li>
                            <li><a href="{{ route('roles.index') }}">Jabatan</a></li>
                        </ul>
                    </li>

                    <li><a href="{{ route('users.index') }}"><i class="feather-users"></i><span>Pengguna</span></a></li>
                    <li><a href="{{ route('user-management.logs') }}"><i class="feather-user-check"></i><span>Log Pengguna</span></a></li>
                @endrole

                {{-- |- - - - - - - - - - - -| --}}
                {{-- |  Modules start here   | --}}
                {{-- |- - - - - - - - - - - -| --}}

                {{--   G O O D  L U C K !  ✨  --}}

                @if (auth()->user()->hasRole('super-admin') ||
                        auth()->user()->canAny(['logs.show.all', 'logs.show.scope', 'logs.show.own']))
                    <li class="menu-title">Lainnya</li>

                    @role('super-admin')
                        <li><a href="{{ route('options.index') }}"><i class="feather-settings"></i><span>Konfigurasi</span></a></li>
                        <li><a href="{{ route('data-initiation.index') }}"><i class="feather-hard-drive"></i><span>Inisialisasi Data</span></a></li>
                    @endrole

                    @canany(['logs.show.all', 'logs.show.scope', 'logs.show.own'])
                        <li><a href="{{ route('logs') }}"><i class="feather-trending-up"></i><span>Log Aktivitas</span></a></li>
                    @endcanany
                @endif
            </ul>
        </div>
    </div>
</div>
