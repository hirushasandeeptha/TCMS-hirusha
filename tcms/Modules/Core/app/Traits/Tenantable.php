<?php

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Support\Facades\Auth;

/**
 * Trait Tenantable
 *
 * Automatically scopes all queries to the authenticated user (Teacher).
 * Apply to any model that stores teacher-specific data.
 *
 * Usage:
 *   class Student extends Model { use Tenantable; }
 *
 *   // Query is auto-scoped:
 *   Student::all(); // only returns authenticated teacher's students
 *
 *   // To temporarily bypass (admin context):
 *   Student::withoutTenant()->all();
 */
trait Tenantable
{
    /**
     * Boot the Tenantable trait — register the global scope automatically.
     */
    public static function bootTenantable(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Set the user_id on creating events.
     */
    public static function bootTenantableEvents(): void
    {
        static::creating(function (Model $model) {
            if (is_null($model->user_id) && Auth::check()) {
                $model->user_id = Auth::id();
            }
        });
    }

    /**
     * Scope to remove tenant filtering (admin access).
     */
    public function scopeWithoutTenant(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
