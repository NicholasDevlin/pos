@php
    $metadata = [
        'title' => 'Ganti Password',
        'breadcrumb' => [],
    ];
    $errorBag = 'updatePassword';
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    {{ html()->form('PUT', route('user-password.update'))->attributes(['autocomplete' => 'off'])->open() }}

                    <div class="form-group mb-4 col-5">
                        @php
                            $label = 'Password Sekarang';
                            $name = 'current_password';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        {{ html()->password($name)->class('form-control' . ($errors->{$errorBag}->has($name) ? ' is-invalid' : '')) }}

                        @error($name, $errorBag)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4 col-5">
                        @php
                            $label = 'Password Baru';
                            $name = 'password';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        {{ html()->password($name)->class('form-control' . ($errors->{$errorBag}->has($name) ? ' is-invalid' : '')) }}

                        <span class="help-block"><small>Password wajib terdiri dari 8 karakter.</small></span>

                        @error($name, $errorBag)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4 col-5">
                        @php
                            $label = 'Konfirmasi Password Baru';
                            $name = 'password_confirmation';
                            $errorName = 'password';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        {{ html()->password($name)->class('form-control' . ($errors->{$errorBag}->has($errorName) ? ' is-invalid' : '')) }}
                    </div>

                    <hr>

                    {{ html()->submit('Ganti Password')->class('btn btn-primary') }}

                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        @if (session('status') === 'password-updated')
            toastr['success']('Password telah berhasil diganti');
        @endif
    </script>
@endpush
