<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function dashboard(): View
    {
        return view('pages.dashboard');
    }

    public function editUserPassword(): View
    {
        return view('pages.edit_user_password');
    }
}
