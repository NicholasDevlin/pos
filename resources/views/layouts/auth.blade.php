<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

@include('partials.head')

<body>
    <div style="background-color: #132843;">
        <div class="container">
            <div class="row">
                <div class="col-6 mx-auto">
                    <div class="d-flex align-items-center min-vh-100">
                        <div class="w-100 d-block bg-white shadow-lg rounded my-5">
                            <div class="row">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.scripts')
    @stack('scripts')
</body>

</html>
