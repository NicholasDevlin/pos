<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex align-items-center">
            <button class="btn btn-sm mr-2 header-item" id="vertical-menu-btn" type="button">
                <i class="fa fa-fw fa-bars"></i>
            </button>
            <div class="header-breadcumb">
                <h6 class="header-pretitle d-none d-md-block">
                    @foreach ($metadata['breadcrumb'] ?? [] as $item)
                        <a href="{{ $item['link'] }}">{{ $item['menu'] }}</a>

                        @if (!$loop->last)
                            <i class="dripicons-arrow-thin-right"></i>
                        @endif
                    @endforeach
                </h6>
                <h2 class="header-title">{{ $metadata['title'] }}</h2>
            </div>
        </div>

        <div class="d-flex align-items-center">
            <div class="dropdown d-inline-block ml-2">
                <button class="btn header-item" id="page-header-user-dropdown" data-toggle="dropdown" type="button" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="{{ Vite::asset('resources/drezoc/images/users/avatar-1.jpg') }}" alt="Header Avatar">
                    <span class="d-none d-sm-inline-block ml-1">{{ auth()->user()->name }}</span>
                    <i class="mdi mdi-chevron-down d-none d-sm-inline-block"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('user-password.edit') }}">
                        <span>Ganti Password</span>
                    </a>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item d-flex align-items-center justify-content-between text-danger" type="submit">
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
