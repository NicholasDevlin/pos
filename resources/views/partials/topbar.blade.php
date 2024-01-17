<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex align-items-center">
            <button class="btn btn-sm mr-2 d-lg-none header-item" id="vertical-menu-btn" type="button">
                <i class="fa fa-fw fa-bars"></i>
            </button>
            <div class="header-breadcumb">
                <h6 class="header-pretitle d-none d-md-block">
                    @foreach ($metadata['breadcrumb'] ?? [] as $item)
                        <a href="{{ $item['link'] }}">{{ $item['menu'] }}</a>

                        @if ($loop->last)
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
                    <span class="d-none d-sm-inline-block ml-1">Henry</span>
                    <i class="mdi mdi-chevron-down d-none d-sm-inline-block"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)">
                        <span>Inbox</span>
                        <span>
                            <span class="badge badge-pill badge-success">3</span>
                        </span>
                    </a>
                    <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)">
                        <span>Profile</span>
                        <span>
                            <span class="badge badge-pill badge-info">1</span>
                        </span>
                    </a>
                    <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)">
                        Settings
                    </a>
                    <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)">
                        <span>Lock Account</span>
                    </a>
                    <a class="dropdown-item d-flex align-items-center justify-content-between" href="javascript:void(0)">
                        <span>Log Out</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
