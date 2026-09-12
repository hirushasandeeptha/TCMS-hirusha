<?php

namespace Modules\Student\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Core\Traits\Tenantable;

/**
 * Student model — strictly isolated to the authenticated teacher via Tenantable.
 *
 * Every student record is linked to a user_id (Teacher).
 * The TenantScope automatically filters queries to the logged-in teacher.
 * JSONB custom_fields provides per-teacher dynamic metadata (school, guardian, etc.).
 */
final class Student extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'user_id',
        'student_code',
        'full_name',
        'phone',
        'qr_code_token',
        'custom_fields',
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Student $student) {
            if (empty($student->student_code)) {
                $student->student_code = self::generateStudentCode($student->user_id);
            }
            if (empty($student->qr_code_token)) {
                $student->qr_code_token = Str::random(40);
            }
        });
    }

    // ────────────────────────────────────────
    //  Relationships
    // ────────────────────────────────────────

    /** The teacher who owns this student record. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /** Attendance records for this student. */
    public function attendances(): HasMany
    {
        return $this->hasMany(\Modules\Attendance\Models\Attendance::class);
    }

    /** Payment records for this student. */
    public function payments(): HasMany
    {
        return $this->hasMany(\Modules\Payment\Models\Payment::class);
    }

    // ────────────────────────────────────────
    //  Helpers
    // ────────────────────────────────────────

    /**
     * Generate a unique student code scoped to the teacher.
     * Format: {teacher_id}-STU-{sequence}
     */
    public static function generateStudentCode(int $userId): string
    {
        $prefix = sprintf('STU-%d-', $userId);
        $last = self::withoutTenant()
            ->where('student_code', 'LIKE', "{$prefix}%")
            ->orderByDesc('student_code')
            ->value('student_code');

        $seq = 1;
        if ($last) {
            $seq = (int) substr($last, strrpos($last, '-') + 1) + 1;
        }

        return sprintf('%s%04d', $prefix, $seq);
    }
}
