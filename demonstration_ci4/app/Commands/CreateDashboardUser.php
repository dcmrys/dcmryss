<?php

namespace App\Commands;

use App\Models\LoginAccountModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateDashboardUser extends BaseCommand
{
    protected $group = 'Accounts';
    protected $name = 'dashboard:user';
    protected $description = 'Create or reset a dashboard login account.';

    public function run(array $params)
    {
        $username = trim(CLI::prompt('Dashboard username or email', null, 'required|max_length[100]'));
        $password = CLI::prompt('Password (at least 8 characters)', null, 'required|min_length[8]');

        $model = new LoginAccountModel();
        $existing = $model->where('username', $username)->first();
        $data = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        if ($existing === null) {
            $model->insert($data);
            CLI::write('Dashboard account created.');
        } else {
            $model->update($existing['id'], $data);
            CLI::write('Dashboard password updated.');
        }
    }
}
