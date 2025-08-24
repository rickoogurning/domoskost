<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'username',
        'email',
        'password',
        'nama_lengkap',
        'no_telp',
        'alamat',
        'foto_profil',
        'is_active',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['role'];

    /**
     * Relationship with Role
     */
    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get role name
     */
    public function getRoleAttribute(): string
    {
        return $this->roleModel?->nama_role ?? '';
    }

    /**
     * Relationship with Penghuni
     */
    public function penghuni(): HasOne
    {
        return $this->hasOne(Penghuni::class);
    }

    /**
     * Relationship with Notifikasi
     */
    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->whereHas('roleModel', function ($q) use ($role) {
            $q->where('nama_role', $role);
        });
    }

    /**
     * Check if user has role
     */
    public function hasRole($role): bool
    {
        return $this->roleModel?->nama_role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->roleModel?->nama_role, $roles);
    }
}
