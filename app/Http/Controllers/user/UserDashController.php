<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;


class UserDashController extends Controller
{
     public function dashboard()
    {
        return view('user.dashboard');
    }
}
