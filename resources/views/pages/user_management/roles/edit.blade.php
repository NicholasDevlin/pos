@php
    $metadata = [
        'title' => ($role->id ? 'Edit' : 'Tambah') . ' Jabatan',
        'breadcrumb' => [['link' => '#', 'menu' => 'Manajemen Pengguna'], ['link' => '#', 'menu' => 'Akses Pengguna'], ['link' => route('roles.index'), 'menu' => 'Jabatan']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    {{ html()->form($role->id ? 'PUT' : 'POST', $role->id ? route('roles.update', [$role->id]) : route('roles.store'))->attributes(['autocomplete' => 'off'])->open() }}
    @php html()->model($role) @endphp

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

                    <div class="form-group mb-4 col-5">
                        @php
                            $label = 'Keterangan';
                            $name = 'notes';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        {{ html()->text($name)->class('form-control' . ($errors->has($name) ? ' is-invalid' : '')) }}

                        @error($name)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="form-group mb-4 col">
                        @php
                            $label = 'Hak Akses';
                            $name = 'permissions[]';
                            $errorName = 'permissions';
                        @endphp
                        {{ html()->label($label, $name) }}

                        <div class="row">
                            @forelse ($permissions as $permissionKey => $permission)
                                <div class="col-md-4 mb-2">
                                    <small>{{ ucfirst(preg_replace('/-+/', ' ', $permissionKey)) }}</small>
                                    <div>
                                        @foreach ($permission as $key => $value)
                                            <div class="form-check">
                                                {{ html()->checkbox($name, false, $key)->checked(old($errorName) !== null ? in_array($key, old($errorName)) : isset($hasPermissions) && in_array($key, $hasPermissions))->id($name . $key)->class('form-check-input' . ($errors->has($errorName) ? ' is-invalid' : '')) }}
                                                {{ html()->label($value, $name . $key)->class('form-check-label') }}

                                                @if ($loop->last && $errors->has($errorName))
                                                    <div class="invalid-feedback">{{ $errors->get($errorName)[0] }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <em>Tidak ada hak akses terdaftar.</em>
                            @endforelse
                        </div>
                    </div>

                    <hr class="log-scopes">

                    <div class="form-group mb-4 col log-scopes">
                        @php
                            $label = 'Skop Log';
                            $name = 'log_scopes[]';
                            $errorName = 'log_scopes';
                        @endphp
                        {{ html()->label($label, $name) }}
                        <span class="text-danger">*</span>

                        <div class="row">
                            @forelse ($models as $model)
                                <div class="col-md-3 mb-2">
                                    <div class="form-check">
                                        {{ html()->checkbox($name, false, $model)->checked(old($errorName) !== null ? in_array($model, old($errorName)) : isset($hasLogScopes) && in_array($model, $hasLogScopes))->id($name . $model)->class('form-check-input' . ($errors->has($errorName) ? ' is-invalid' : '')) }}
                                        {{ html()->label($model, $name . $model)->class('form-check-label') }}

                                        @if ($loop->last && $errors->has($errorName))
                                            <div class="invalid-feedback">{{ $errors->get($errorName)[0] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <em>Tidak ada skop log terdaftar.</em>
                            @endforelse
                        </div>
                    </div>

                    <hr>

                    {{ html()->submit($role->id ? 'Update' : 'Simpan')->class('btn btn-primary')->style(['position' => 'sticky', 'bottom' => '30px']) }}
                </div>
            </div>
        </div>
    </div>

    @php html()->endModel() @endphp
    {{ html()->form()->close() }}
@endsection

@push('scripts')
    <script>
        $(function() {
            // To retain scroll position on refresh
            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'auto';
            }

            // To retain scroll position on Laravel redirect
            $('form').on('submit', function() {
                localStorage.setItem('role-page-scroll-position', window.scrollY);
            });
            const savedScrollPosition = localStorage.getItem('role-page-scroll-position');
            if (savedScrollPosition) {
                window.scrollTo(0, parseInt(savedScrollPosition, 10));
                localStorage.removeItem('scrollPosition'); // Clean up
            }

            $("input[value='logs.show.scope']").trigger("change");
        });

        $("input[value='logs.show.scope']").change(function() {
            if ($(this).is(":checked")) {
                $(".log-scopes").show();
            } else {
                $(".log-scopes").hide();
            }
        });
    </script>
@endpush
