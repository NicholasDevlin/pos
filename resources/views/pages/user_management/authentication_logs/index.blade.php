@php
    $metadata = [
        'title' => 'Log Pengguna',
        'breadcrumb' => [['link' => '#', 'menu' => 'Manajemen Pengguna']],
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#login-tab" aria-expanded="false">
                                <span>Login</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#register-tab" aria-expanded="true">
                                <span>Register</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#edit-password-tab" aria-expanded="false">
                                <span>Ganti Password</span>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="login-tab">
                            <div id="login"></div>
                        </div>
                        <div class="tab-pane show" id="register-tab">
                            <div id="register"></div>
                        </div>
                        <div class="tab-pane" id="edit-password-tab">
                            <div id="edit-password"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('pages.user_management.authentication_logs._login')
@include('pages.user_management.authentication_logs._register')
@include('pages.user_management.authentication_logs._edit_password')
