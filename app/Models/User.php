<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Super admin — akses penuh termasuk CMS & kelola pengguna
    public const ROLE_ADMIN        = 'admin';
    // Manager pusat — akses Readonly di eksekutif/seluruh unit
    public const ROLE_MANAGER_PUSAT= 'manager_pusat';
    // Admin per unit — akses unit simpan pinjam saja, tanpa CMS
    public const ROLE_ADMIN_UNIT   = 'admin_unit';
    // Manager per unit — akses terbatas di unit simpan pinjam
    public const ROLE_MANAGER      = 'manager';

    public static array $allRoles = [
        self::ROLE_ADMIN         => 'Super Admin',
        self::ROLE_MANAGER_PUSAT => 'Manager Pusat',
        self::ROLE_ADMIN_UNIT    => 'Admin Unit',
        self::ROLE_MANAGER       => 'Manager Unit',
    ];

    protected $fillable = [
        'nama',
        'email',
        'role',
        'password',
        'unit_id',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /** Apakah user ini super admin (akses penuh termasuk CMS) */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Apakah user ini bisa mengakses unit simpan pinjam */
    public function canAccessUnit(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER_PUSAT, self::ROLE_ADMIN_UNIT, self::ROLE_MANAGER]);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
