@php
    $metadata = [
        'title' => 'Lihat Hak Akses',
        'breadcrumb' => [['link' => '#', 'menu' => 'Manajemen Pengguna'], ['link' => '#', 'menu' => 'Akses Pengguna'], ['link' => route('permissions.index'), 'menu' => 'Hak Akses']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Info Hak Akses</div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">ID</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $permission->id }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Nama</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $permission->name }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-3 col-lg-2">Keterangan</div>
                        <div class="col-12 col-md-9 col-lg-10 font-weight-bold">{{ $permission->notes }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="card-title mb-3">Jabatan dan Pengguna</div>

                    @if ($permission->roles->count())
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th class="col-4">Jabatan</th>
                                    <th class="col-8">Pengguna</th>
                                </tr>
                            </thead>
                            @foreach ($permission->roles as $role)
                                <tbody>
                                    @if ($role->users->count())
                                        @foreach ($role->users as $user)
                                            <tr>
                                                @if ($loop->first)
                                                    <td rowspan="{{ $loop->count }}">{{ $role->notes }} (<small>{{ $role->name }}</small>)</td>
                                                @endif
                                                <td>{{ $user->name }} (<small>{{ '@' . $user->username }}</small>)</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            @endforeach
                        </table>
                    @else
                        <em>Tidak ada jabatan terdaftar.</em>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
