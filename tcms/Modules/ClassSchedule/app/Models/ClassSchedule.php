<?php

namespace Modules\ClassSchedule\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\Tenantable;

/**
 * ClassSchedule model — represents a recurring class session.
 * Strictly isolated to the authenticated teacher via Tenantable.
 */
final class ClassSchedule extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'user_id',
        'name',
        'subject',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
        'is_active'  => 'boolean',
        'meta'       => 'array',
    ];

    // ────────────────────────────────────────
    //  Relationships
    // ────────────────────────────────────────

    /** The teacher who owns this schedule. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /** Attendance records linked to this class session. */
    public function attendances(): HasMany
    {
        return $this->hasMany(\Modules\Attendance\Models\Attendance::class);
    }

    // ────────────────────────────────────────
    //  Scopes
    // ────────────────────────────────────────

    /** Filter to active schedules only. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Filter to a specific day of the week. */
    public function scopeForDay($query, string $day)
    {
        return $query->where('day_of_week', strtolower($day));
    }
}
