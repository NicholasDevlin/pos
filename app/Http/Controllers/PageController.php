<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;

class PageController extends Controller
{
    public function dashboard(): Renderable
    {
        return view('pages.dashboard');
    }

    public function editUserPassword(): Renderable
    {
        return view('pages.edit_user_password');
    }
}
