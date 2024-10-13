<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = ['name'];

    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN = 'admin';
    public const USER = 'user';
    public const ARTHUR = 'arthur';

    public static function allRoles(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::USER,
            self::ARTHUR
        ];
    }

    public static function getPermissionsForRole($role): array
    {
        switch ($role) {
            case self::SUPER_ADMIN:
                return Permission::pluck('name')->toArray();
            case self::ADMIN:
                return [
                    'view_items',
                    'create_items',
                    'edit_items',
                    'update_items',
                    'delete_items',
                    'view_users',
                    'create_users',
                    'edit_users',
                    'update_users',
                    'delete_users',
                ];
            case self::ARTHUR:
                return [
                    'view_items',
                    'create_items',
                    'edit_items',
                    'update_items',
                    'delete_items',
                ];
            case self::USER:
                return ['view_items'];
            default:
                return [];
        }
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }
}
