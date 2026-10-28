<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'member_number', 'first_name', 'last_name', 'email', 'phone', 'address',
        'membership_category_id', 'membership_level_id', 'status', 'joined_at', 'expires_at', 'notes',
    ];

    protected function casts(): array
    {
        return ['joined_at' => 'date', 'expires_at' => 'date'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(MembershipLevel::class, 'membership_level_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'active' && $this->expires_at?->lt(today())) {
            return 'expired';
        }

        return $this->status;
    }
}
