@extends('layouts.auth')

@section('content')
    <div class="col-lg-12">
        <div class="p-4 p-lg-5">
            @include('partials.login_logo')
            <h1 class="h5 m-0 font-weight-bold text-center">{{ config('app.name', 'Laravel') }}</h1>
            <p class="text-center text-muted mb-4" style="font-size: 0.8rem;">{{ config('app.version', 'v1.0.0') }}</p>

            {{ html()->form('POST', route('register'))->attributes(['autocomplete' => 'off'])->open() }}

            <div class="form-group">
                @php
                    $label = 'Nama';
                    $name = 'name';
                @endphp
                {{ html()->text($name, old($name))->placeholder($label)->class('form-control form-control-user' . ($errors->has($name) ? ' is-invalid' : '')) }}

                @error($name)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr>

            <div class="form-group">
                @php
                    $label = 'Username';
                    $name = 'username';
                @endphp
                {{ html()->text($name, old($name))->placeholder($label)->class('form-control form-control-user' . ($errors->has($name) ? ' is-invalid' : '')) }}

                @error($name)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                @php
                    $label = 'Password';
                    $name = 'password';
                @endphp
                {{ html()->password($name)->placeholder($label)->class('form-control form-control-user' . ($errors->has($name) ? ' is-invalid' : '')) }}

                @error($name)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                @php
                    $label = 'Konfirmasi Password';
                    $name = 'password_confirmation';
                    $errorName = 'password';
                @endphp
                {{ html()->password($name)->placeholder($label)->class('form-control form-control-user' . ($errors->has($errorName) ? ' is-invalid' : '')) }}
            </div>

            {{ html()->submit('Register')->class('btn btn-primary btn-block') }}

            {{ html()->form()->close() }}
        </div>
    </div>
@endsection
