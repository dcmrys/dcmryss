<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MembershipApplication extends Model
{
    protected $fillable = [
        'membership_category_id', 'reference_no', 'full_name', 'email',
        'phone', 'address', 'latitude', 'longitude', 'status', 'notification_queued_at',
    ];

    protected function casts(): array
    {
        return ['notification_queued_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(MembershipTransaction::class, 'membership_application_id');
    }
}
