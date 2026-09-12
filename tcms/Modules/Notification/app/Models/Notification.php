<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\Tenantable;

/**
 * Notification model — tracks messages sent to students/parents.
 * Strictly isolated to the authenticated teacher via Tenantable.
 */
final class Notification extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'user_id',
        'student_id',
        'type',
        'title',
        'body',
        'channel',
        'status',
        'sent_at',
        'read_at',
        'meta',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'meta'    => 'array',
    ];

    // ────────────────────────────────────────
    //  Relationships
    // ────────────────────────────────────────

    /** The teacher who owns this notification. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /** The student this notification is about (nullable for general notifications). */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\Modules\Student\Models\Student::class);
    }

    // ────────────────────────────────────────
    //  Helpers
    // ────────────────────────────────────────

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now(), 'status' => 'read']);
        }
    }

    // ────────────────────────────────────────
    //  Scopes
    // ────────────────────────────────────────

    /** Filter to unsent drafts only. */
    public function scopeDrafts($query)
    {
        return $query->where('status', 'draft');
    }

    /** Filter to sent notifications only. */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /** Filter to unread notifications only. */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /** Filter by notification type. */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /** Filter by delivery channel. */
    public function scopeViaChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }
}
