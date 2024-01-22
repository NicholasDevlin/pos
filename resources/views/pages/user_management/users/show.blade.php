@php
    $metadata = [
        'title' => 'Lihat Pengguna',
        'breadcrumb' => [['link' => '#', 'menu' => 'Manajemen Pengguna'], ['link' => route('users.index'), 'menu' => 'Pengguna']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Info Pengguna</div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">ID</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $user->id }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Nama</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $user->name }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Username</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $user->username }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Status</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{!! $user->statusLabel() !!}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Jabatan</div>

                    <div class="row">
                        @forelse ($roles as $roleKey => $role)
                            <div class="col-md-3 mb-2">
                                <div>{{ in_array($roleKey, $hasRoles) ? '✅' : '❌' }} {{ $role }}</div>
                            </div>
                        @empty
                            <em>Tidak ada model terdaftar.</em>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Skop Akses</div>

                    <div class="row">
                        @forelse ($locations as $locationKey => $location)
                            <div class="col-md-4 mb-3">
                                <small>{{ ucfirst($location) }}</small>
                                @foreach ($divisions as $divisionKey => $division)
                                    <div>{{ in_array("[$locationKey][$divisionKey]", $hasScopes) ? '✅' : '❌' }} {{ $division }}</div>
                                @endforeach
                            </div>
                        @empty
                            <em>Tidak ada skop akses terdaftar.</em>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
