<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\Tenantable;

/**
 * Payment model — tracks monthly fee payments per student.
 * Strictly isolated to the authenticated teacher via Tenantable.
 * JSONB receipt_meta provides flexible payment receipt data.
 */
final class Payment extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'user_id',
        'student_id',
        'month',
        'amount',
        'discount_type',
        'receipt_meta',
    ];

    protected $casts = [
        'month'        => 'date',
        'amount'       => 'decimal:2',
        'receipt_meta' => 'array',
    ];

    // ────────────────────────────────────────
    //  Relationships
    // ────────────────────────────────────────

    /** The teacher who owns this payment record. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /** The student this payment is for. */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\Modules\Student\Models\Student::class);
    }

    // ────────────────────────────────────────
    //  Accessors & Helpers
    // ────────────────────────────────────────

    /**
     * Compute the effective amount after discount.
     */
    public function getEffectiveAmountAttribute(): float
    {
        return match ($this->discount_type) {
            'full_free' => 0.0,
            'half_free' => $this->amount / 2,
            default     => $this->amount,
        };
    }

    /**
     * Check if this payment is fully paid (no outstanding balance).
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->discount_type === 'full_free'
            || ($this->receipt_meta['status'] ?? 'paid') === 'paid';
    }

    // ────────────────────────────────────────
    //  Scopes
    // ────────────────────────────────────────

    /** Filter to a specific billing month. */
    public function scopeForMonth($query, string $month)
    {
        return $query->where('month', $month);
    }

    /** Filter to paid payments only. */
    public function scopePaid($query)
    {
        return $query->where('receipt_meta->>', 'status', 'paid');
    }

    /** Filter to unpaid payments only. */
    public function scopeUnpaid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull("receipt_meta->>'status'")
              ->orWhere("receipt_meta->>'status'", '!=', 'paid');
        });
    }
}
