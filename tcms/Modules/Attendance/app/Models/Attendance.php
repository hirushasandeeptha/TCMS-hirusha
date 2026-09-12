<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\Tenantable;

/**
 * Attendance record — tracks student presence per class session.
 * Strictly isolated to the authenticated teacher via Tenantable.
 */
final class Attendance extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'user_id',
        'student_id',
        'class_schedule_id',
        'status',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    // ────────────────────────────────────────
    //  Relationships
    // ────────────────────────────────────────

    /** The teacher who owns this attendance record. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /** The student this attendance record belongs to. */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\Modules\Student\Models\Student::class);
    }

    /** The class schedule this attendance is recorded for. */
    public function classSchedule(): BelongsTo
    {
        return $this->belongsTo(\Modules\ClassSchedule\Models\ClassSchedule::class);
    }

    // ────────────────────────────────────────
    //  Scopes
    // ────────────────────────────────────────

    /** Filter to present students only. */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /** Filter to absent students only. */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /** Filter to late students only. */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }
}
