<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

@include('partials.head')

<body>
    <div id="layout-wrapper">
        @include('partials.sidebar')

        <div class="main-content">
            @include('partials.topbar')

            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>

            @include('partials.footer')
        </div>
    </div>

    <div class="menu-overlay"></div>

    @include('partials.scripts')
</body>

</html>
