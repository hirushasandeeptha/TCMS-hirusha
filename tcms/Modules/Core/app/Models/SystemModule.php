<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class SystemModule extends Model
{
    protected $fillable = [
        'name',
        'slug', 'description', 'price',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /** Teachers who have subscribed to this module */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'user_modules',
            'system_module_id',
            'user_id'
        )->withPivot(['subscribed_at', 'expires_at', 'status'])
          ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
