<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipTransaction extends Model
{
    protected $fillable = [
        'reference_no', 'membership_application_id', 'member_id', 'membership_category_id',
        'membership_level_id', 'type', 'status', 'amount_due', 'starts_on', 'ends_on',
        'processed_at', 'notes',
    ];

    public const STATUSES = ['pending', 'paid', 'approved', 'expired', 'cancelled'];

    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'processed_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class, 'membership_application_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'membership_category_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(MembershipLevel::class, 'membership_level_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalanceAttribute(): float
    {
        $paid = $this->relationLoaded('payments')
            ? $this->payments->sum(fn (Payment $payment) => (float) $payment->amount)
            : $this->payments()->sum('amount');

        return max(0, round((float) $this->amount_due - (float) $paid, 2));
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->amount_due - $this->balance;
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'approved' && $this->ends_on?->lt(today())) {
            return 'expired';
        }

        return $this->status;
    }
}
