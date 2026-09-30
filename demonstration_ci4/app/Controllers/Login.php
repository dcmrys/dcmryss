<?php

namespace App\Controllers;

use App\Models\LoginAccountModel;

class Login extends BaseController
{
    public function index()
    {
        if (session()->get('dashboard_logged_in') === true) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('login', [
            'title' => 'Login - Puihaha Electric',
            'page' => 'login',
        ]);
    }

    public function authenticate()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (! is_string($username) || ! is_string($password)) {
            return redirect()->to(site_url('login'))->with('error', 'Invalid username or password.');
        }

        $username = trim($username);
        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|max_length[100]',
            'password' => 'required',
        ]);

        if (! $validation->run(['username' => $username, 'password' => $password])) {
            return redirect()->to(site_url('login'))->with('error', 'Enter your username and password.');
        }

        $account = (new LoginAccountModel())->where('username', $username)->first();

        if ($account === null || ! password_verify($password, $account['password'])) {
            return redirect()->to(site_url('login'))->with('error', 'Invalid username or password.');
        }

        session()->regenerate(true);
        session()->set([
            'dashboard_logged_in' => true,
            'dashboard_user_id' => $account['id'],
            'dashboard_username' => $account['username'],
        ]);

        return redirect()->to(site_url('dashboard'));
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(site_url('login'));
    }
}
