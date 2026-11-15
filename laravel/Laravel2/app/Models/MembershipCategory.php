<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipCategory extends Model
{
    protected $fillable = ['name', 'description'];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(MembershipApplication::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class, 'membership_category_id');
    }
}
