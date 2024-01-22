@php
    $metadata = [
        'title' => 'Lihat Jabatan',
        'breadcrumb' => [['link' => '#', 'menu' => 'Manajemen Pengguna'], ['link' => '#', 'menu' => 'Akses Pengguna'], ['link' => route('roles.index'), 'menu' => 'Jabatan']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Info Jabatan</div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">ID</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $role->id }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Nama</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $role->name }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Keterangan</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $role->notes }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Hak Akses</div>

                    <div class="row">
                        @forelse ($permissions as $permissionKey => $permission)
                            <div class="col-md-4 mb-3">
                                <small>{{ ucfirst(preg_replace('/-+/', ' ', $permissionKey)) }}</small>
                                @foreach ($permission as $key => $value)
                                    <div>{{ in_array($key, $hasPermissions) ? '✅' : '❌' }} {{ $value }}</div>
                                @endforeach
                            </div>
                        @empty
                            <em>Tidak ada hak akses terdaftar.</em>
                        @endforelse
                    </div>
                </div>
            </div>

            @if ($hasLogScopes)
                <div class="card">
                    <div class="card-body">
                        <div class="card-title mb-3">Skop Log</div>

                        <div class="row">
                            @forelse ($models as $model)
                                <div class="col-md-3 mb-2">
                                    <div>{{ in_array($model, $hasLogScopes) ? '✅' : '❌' }} {{ $model }}</div>
                                </div>
                            @empty
                                <em>Tidak ada model terdaftar.</em>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Pengguna</div>

                    @forelse ($role->users as $user)
                        <li class="col-md-3">{{ $user->name }}</li>
                    @empty
                        <em>Tidak ada pengguna terdaftar.</em>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
