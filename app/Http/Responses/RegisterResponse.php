<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    protected StatefulGuard $guard;

    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    public function toResponse($request): JsonResponse|Response
    {
        $this->guard->logout();

        Session::flash('success', 'Registrasi berhasil. Silakan hubungi tim IT untuk melakukan aktivasi akun.');

        return $request->wantsJson()
            ? new JsonResponse('', Response::HTTP_CREATED)
            : redirect(config('fortify.registered'));
    }
}
