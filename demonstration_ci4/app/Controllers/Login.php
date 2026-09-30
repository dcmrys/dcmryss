<?php

namespace App\Controllers;

class Login extends BaseController
{
    public function index(): string
    {
        return view('login', [
            'title' => 'Login - Puihaha Electric',
            'page' => 'login',
        ]);
    }
}
