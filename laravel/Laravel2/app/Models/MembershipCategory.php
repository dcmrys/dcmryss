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
}
