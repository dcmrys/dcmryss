<?php

namespace App\Controllers;

use App\Models\AuthUserModel;

class Auth extends BaseController
{
    public function entry()
    {
        return redirect()->to(site_url(session()->has('auth_user_id') ? 'dashboard' : 'login'));
    }

    public function loginForm()
    {
        if (session()->has('auth_user_id')) {
            return redirect()->to(site_url('dashboard'));
        }

        if ((new AuthUserModel())->countAllResults() === 0) {
            return redirect()->to(site_url('setup'));
        }

        return view('auth/login', [
            'error' => session()->getFlashdata('error'),
            'success' => session()->getFlashdata('success'),
            'email' => session()->getFlashdata('email'),
        ]);
    }

    public function login()
    {
        if (session()->has('auth_user_id')) {
            return redirect()->to(site_url('dashboard'));
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        $throttleKey = 'login_' . hash('sha256', $this->request->getIPAddress());
        if (! service('throttler')->check($throttleKey, 10, 60)) {
            return redirect()->to(site_url('login'))->with('error', 'Too many login attempts. Try again in a minute.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return redirect()->to(site_url('login'))->with('error', 'Enter a valid email and password.')->with('email', $email);
        }

        $user = (new AuthUserModel())->where('email', $email)->first();
        if ($user === null || ! password_verify($password, $user['password_hash'])) {
            return redirect()->to(site_url('login'))->with('error', 'Invalid email or password.')->with('email', $email);
        }

        $session = session();
        $session->regenerate(true);
        $session->set([
            'auth_user_id' => $user['id'],
            'auth_user_name' => $user['name'],
        ]);

        return redirect()->to(site_url('dashboard'));
    }

    public function setupForm()
    {
        if (! $this->isLocalRequest()) {
            return $this->response->setStatusCode(403)->setBody('Initial setup is available only from this computer.');
        }

        if ((new AuthUserModel())->countAllResults() !== 0) {
            return redirect()->to(site_url('login'));
        }

        return view('auth/setup', [
            'errors' => session()->getFlashdata('errors') ?? [],
            'name' => session()->getFlashdata('name') ?? '',
            'email' => session()->getFlashdata('email') ?? '',
        ]);
    }

    public function setup()
    {
        if (! $this->isLocalRequest()) {
            return $this->response->setStatusCode(403)->setBody('Initial setup is available only from this computer.');
        }

        $model = new AuthUserModel();
        if ($model->countAllResults() !== 0) {
            return redirect()->to(site_url('login'));
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $confirmation = (string) $this->request->getPost('confirm_password');

        $validation = service('validation');
        $validation->setRules([
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[191]',
            'password' => 'required|min_length[12]',
            'confirm_password' => 'required|matches[password]',
        ]);

        if (! $validation->run(compact('name', 'email', 'password') + ['confirm_password' => $confirmation])) {
            return redirect()->to(site_url('setup'))
                ->with('errors', $validation->getErrors())
                ->with('name', $name)
                ->with('email', $email);
        }

        $model->insert([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return redirect()->to(site_url('login'))->with('success', 'Admin account created. Sign in to open the dashboard.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(site_url('login'));
    }

    private function isLocalRequest(): bool
    {
        return in_array($this->request->getIPAddress(), ['127.0.0.1', '::1'], true);
    }
}
