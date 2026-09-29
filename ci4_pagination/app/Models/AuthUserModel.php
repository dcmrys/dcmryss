<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthUserModel extends Model
{
    protected $table = 'auth_users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'email', 'password_hash'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
