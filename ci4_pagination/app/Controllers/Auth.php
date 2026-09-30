<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function entry()
    {
        return redirect()->to(site_url('login'));
    }

    public function loginForm()
    {
        return view('auth/login');
    }
}
