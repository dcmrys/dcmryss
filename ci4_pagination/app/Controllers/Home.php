<?php

namespace App\Controllers;

use App\Models\CustomerAccountModel;
use CodeIgniter\Controller;

class Home extends Controller
{
    protected $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerAccountModel();
    }

    /**
     * Display dashboard with customer accounts list (paginated)
     */
    public function index()
    {
        // Get search keyword if exists
        $keyword = $this->request->getGet('search');
        $keyword = is_string($keyword) ? trim($keyword) : '';
        $status = $this->request->getGet('status');
        $status = is_string($status) && in_array($status, ['active', 'inactive', 'suspended'], true) ? $status : '';
        $type = $this->request->getGet('type');
        $type = is_string($type) && in_array($type, ['residential', 'commercial', 'industrial'], true) ? $type : '';

        // Items per page
        $perPage = 10;

        $accounts = $this->customerModel->getFilteredAccounts($keyword, $status, $type, $perPage);
        $statistics = new CustomerAccountModel();

        // Get statistics
        $data = [
            'accounts' => $accounts,
            'pager' => $this->customerModel->pager,
            'total_accounts' => $statistics->getTotalAccounts(),
            'active_accounts' => $statistics->getCountByStatus('active'),
            'inactive_accounts' => $statistics->getCountByStatus('inactive'),
            'suspended_accounts' => $statistics->getCountByStatus('suspended'),
            'current_page' => $this->request->getGet('page') ?? 1,
            'search_keyword' => $keyword,
            'filter_status' => $status,
            'filter_type' => $type
        ];

        return view('home/index', $data);
    }

    /**
     * View single account details
     */
    public function viewAccount($id)
    {
        $account = $this->customerModel->find($id);

        if (!$account) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Account not found');
        }

        $data = [
            'account' => $account
        ];

        return view('home/view_account', $data);
    }
}
