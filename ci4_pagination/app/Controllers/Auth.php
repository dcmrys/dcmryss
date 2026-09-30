<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function loginForm()
    {
        return redirect()->to('http://localhost/dcmryss/demonstration_ci4/public/index.php/login');
    }
}
