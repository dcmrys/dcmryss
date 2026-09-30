<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequireLogin implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('dashboard_logged_in') === true && session()->get('dashboard_user_id') !== null) {
            return null;
        }

        return redirect()->to(site_url('login'))->with('error', 'Please sign in to open the dashboard.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No response changes are needed.
    }
}
