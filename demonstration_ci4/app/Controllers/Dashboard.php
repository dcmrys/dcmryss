<?php

namespace App\Controllers;

use App\Models\CustomerAccountModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

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

    public function newAccount(): string
    {
        return view('dashboard/account_form', ['account' => null]);
    }

    public function createAccount()
    {
        $data = $this->accountInput();
        $errors = $this->accountErrors($data);

        if ($errors !== []) {
            return redirect()->to(site_url('account/new'))->withInput()->with('errors', $errors);
        }

        try {
            $id = (new CustomerAccountModel())->insert($data);
        } catch (DatabaseException $exception) {
            return redirect()->to(site_url('account/new'))->withInput()->with('errors', ['account_number' => 'Could not save this account. Check that the account number is unique.']);
        }

        return redirect()->to(site_url('account/' . $id))->with('success', 'Account created.');
    }

    public function editAccount(int $id)
    {
        $account = (new CustomerAccountModel())->find($id);

        if ($account === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Account not found.');
        }

        return view('dashboard/account_form', ['account' => $account]);
    }

    public function updateAccount(int $id)
    {
        $model = new CustomerAccountModel();
        if ($model->find($id) === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Account not found.');
        }

        $data = $this->accountInput();
        $errors = $this->accountErrors($data, $id);

        if ($errors !== []) {
            return redirect()->to(site_url('account/' . $id . '/edit'))->withInput()->with('errors', $errors);
        }

        try {
            $model->update($id, $data);
        } catch (DatabaseException $exception) {
            return redirect()->to(site_url('account/' . $id . '/edit'))->withInput()->with('errors', ['account_number' => 'Could not save this account. Check that the account number is unique.']);
        }

        return redirect()->to(site_url('account/' . $id))->with('success', 'Account updated.');
    }

    public function deleteAccount(int $id)
    {
        $model = new CustomerAccountModel();
        if ($model->find($id) === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Account not found.');
        }

        $model->delete($id);

        return redirect()->to(site_url('dashboard'))->with('success', 'Account deleted.');
    }

    private function accountInput(): array
    {
        $fields = ['account_number', 'customer_name', 'address', 'phone', 'email', 'meter_number', 'connection_type', 'status'];
        $data = [];

        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : '';
        }

        foreach (['phone', 'email', 'meter_number'] as $field) {
            if ($data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    private function accountErrors(array $data, ?int $currentId = null): array
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'account_number' => 'required|max_length[50]',
            'customer_name' => 'required|max_length[150]',
            'address' => 'required|max_length[1000]',
            'phone' => 'permit_empty|max_length[20]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'meter_number' => 'permit_empty|max_length[50]',
            'connection_type' => 'required|in_list[residential,commercial,industrial]',
            'status' => 'required|in_list[active,inactive,suspended]',
        ]);

        $validation->run($data);
        $errors = $validation->getErrors();

        if (! isset($errors['account_number'])) {
            $existing = (new CustomerAccountModel())->where('account_number', $data['account_number'])->first();
            if ($existing !== null && (int) $existing['id'] !== $currentId) {
                $errors['account_number'] = 'This account number is already in use.';
            }
        }

        return $errors;
    }
}
