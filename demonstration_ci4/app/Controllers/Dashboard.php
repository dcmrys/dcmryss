<?php

namespace App\Controllers;

use App\Models\CustomerAccountModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $keyword = $this->request->getGet('search');
        $keyword = is_string($keyword) ? trim($keyword) : '';

        $status = $this->request->getGet('status');
        $status = is_string($status) && in_array($status, ['active', 'inactive', 'suspended'], true) ? $status : '';

        $type = $this->request->getGet('type');
        $type = is_string($type) && in_array($type, ['residential', 'commercial', 'industrial'], true) ? $type : '';

        $accounts = new CustomerAccountModel();
        $rows = $accounts->getFilteredAccounts($keyword, $status, $type, 10);

        return view('dashboard/index', [
            'accounts' => $rows,
            'pager' => $accounts->pager,
            'total_accounts' => (new CustomerAccountModel())->getTotalAccounts(),
            'active_accounts' => (new CustomerAccountModel())->getCountByStatus('active'),
            'inactive_accounts' => (new CustomerAccountModel())->getCountByStatus('inactive'),
            'suspended_accounts' => (new CustomerAccountModel())->getCountByStatus('suspended'),
            'search_keyword' => $keyword,
            'filter_status' => $status,
            'filter_type' => $type,
        ]);
    }

    public function viewAccount(int $id)
    {
        $account = (new CustomerAccountModel())->find($id);

        if ($account === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Account not found');
        }

        return view('dashboard/view_account', ['account' => $account]);
    }
}
