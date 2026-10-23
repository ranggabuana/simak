<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'kecamatan_id', 'nip', 'jabatan'])]
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

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kecamatan_id');
    }

    public function canAccessMenu(string $menuKey): bool
    {
        if ($this->role === 'admin' || $this->role === 'superadmin') {
            return true;
        }

        static $permissionCache = [];
        if (! isset($permissionCache[$this->role])) {
            $permissionCache[$this->role] = RoleMenuPermission::getPermissionsForRole($this->role);
        }

        return in_array($menuKey, $permissionCache[$this->role], true);
    }

    /**
     * @param  array<int, string>  $menuKeys
     */
    public function canAccessAnyMenu(array $menuKeys): bool
    {
        foreach ($menuKeys as $key) {
            if ($this->canAccessMenu($key)) {
                return true;
            }
        }

        return false;
    }

    public function getRoleLabelAttribute(): string
    {
        $roles = RoleMenuPermission::getRoles();

        return $roles[$this->role]['label'] ?? ucfirst($this->role);
    }

    public function getRoleBadgeAttribute(): string
    {
        $roles = RoleMenuPermission::getRoles();

        return $roles[$this->role]['badge'] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }
}
