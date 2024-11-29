@php
    $metadata = [
        'title' => ($user->id ? 'Edit' : 'Tambah') . ' Pengguna',
        'breadcrumb' => [['link' => '#', 'menu' => 'Manajemen Pengguna'], ['link' => route('users.index'), 'menu' => 'Pengguna']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    {{ html()->form($user->id ? 'PUT' : 'POST', $user->id ? route('users.update', [$user->id]) : route('users.store'))->attributes(['autocomplete' => 'off'])->open() }}
    @php html()->model($user) @endphp

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group mb-4 col-5">
                        @php
                            $label = 'Nama';
                            $name = 'name';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        {{ html()->text($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : '')) }}

                        @error($name)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="form-group mb-4 col-5">
                        @php
                            $label = 'Username';
                            $name = 'username';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        {{ html()->text($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : ''))->disabled(isset($user->id)) }}

                        @error($name)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4 col-5">
                        @php
                            $label = 'Password';
                            $name = 'password';
                        @endphp
                        {{ html()->label($label, $name) }}
                        @if (!$user->id)
                            <span class="text-danger">*</span>
                        @endif

                        {{ html()->password($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : '')) }}

                        <span class="help-block"><small>Password wajib terdiri dari 8 karakter.</small></span>

                        @error($name)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4 col-5">
                        @php
                            $label = 'Status';
                            $name = 'status';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        @foreach ($statusList as $key => $status)
                            <div class="form-check">
                                {{ html()->radio($name, false, $key)->checked(old($name) !== null ? old($name) == $key : $user->{$name} !== null && $user->{$name} == $key)->class('form-check-input' . ($errors->has($name) ? ' is-invalid' : '')) }}
                                {{ html()->label($status, "{$name}_{$key}")->class('form-check-label') }}

                                @if ($loop->last && $errors->has($name))
                                    <div class="invalid-feedback">{{ $errors->get($name)[0] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <hr>

                    <div class="form-group mb-4 col">
                        @php
                            $label = 'Jabatan';
                            $name = 'roles[]';
                            $errorName = 'roles';
                        @endphp
                        {{ html()->label($label, $name) }}

                        <div class="row">
                            @forelse ($roles as $roleKey => $role)
                                <div class="col-md-3 mb-2">
                                    <div class="form-check">
                                        {{ html()->checkbox($name, false, $roleKey)->checked(old($errorName) !== null ? in_array($roleKey, old($errorName)) : isset($hasRoles) && in_array($roleKey, $hasRoles))->id($name . $roleKey)->class('form-check-input' . ($errors->has($errorName) ? ' is-invalid' : '')) }}
                                        {{ html()->label($role, $name . $roleKey)->class('form-check-label') }}

                                        @if ($loop->last && $errors->has($errorName))
                                            <div class="invalid-feedback">{{ $errors->get($errorName)[0] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <em>Tidak ada jabatan terdaftar.</em>
                            @endforelse
                        </div>
                    </div>

                    <hr>

                    <div class="form-group mb-4 col">
                        @php
                            $label = 'Skop Akses';
                            $name = 'scopes[]';
                            $errorName = 'scopes';
                        @endphp
                        {{ html()->label($label, $name) }}

                        <div class="row">
                            @forelse ($locations as $locationKey => $location)
                                <div class="col-md-4 mb-2">
                                    <small>{{ ucfirst($location) }}</small>
                                    <div>
                                        @foreach ($businessUnits as $businessUnitKey => $businessUnit)
                                            <div class="form-check">
                                                {{ html()->checkbox("scopes[$locationKey][]", false, $businessUnitKey)->checked(old($errorName) !== null ? in_array($businessUnitKey, old($errorName)[$locationKey]) : isset($hasScopes) && in_array("[$locationKey][$businessUnitKey]", $hasScopes))->id($name . $locationKey . $businessUnitKey)->class('form-check-input' . ($errors->has($errorName) ? ' is-invalid' : '')) }}
                                                {{ html()->label($businessUnit, $name . $locationKey . $businessUnitKey)->class('form-check-label') }}

                                                @if ($loop->last && $errors->has($errorName))
                                                    <div class="invalid-feedback">{{ $errors->get($errorName)[0] }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <em>Tidak ada skop akses terdaftar.</em>
                            @endforelse
                        </div>
                    </div>

                    <hr>

                    {{ html()->submit($user->id ? 'Update' : 'Simpan')->class('btn btn-primary') }}
                </div>
            </div>
        </div>
    </div>

    @php html()->endModel() @endphp
    {{ html()->form()->close() }}
@endsection
