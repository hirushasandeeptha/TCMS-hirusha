<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Core\Models\SystemModule;

#[Fillable(['name', 'email', 'password', 'role', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Modules this user has subscribed to.
     */
    public function subscribedModules(): BelongsToMany
    {
        return $this->belongsToMany(
            SystemModule::class,
            'user_modules',
            'user_id',
            'system_module_id'
        )->withPivot(['subscribed_at', 'expires_at', 'status'])
          ->withTimestamps();
    }

    /**
     * Check if the user has access to a specific module.
     */
    public function hasModuleAccess(string $moduleSlug): bool
    {
        return $this->subscribedModules()
            ->where('system_modules.slug', $moduleSlug)
            ->where('user_modules.status', 'active')
            ->exists();
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}
