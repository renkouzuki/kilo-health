<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory  , SoftDeletes;

    protected $fillable = ['name'];

    public static function defaultPermissions(): array {
        return [
            'view_items',
            'create_items',
            'edit_items',
            'update_items',
            'delete_items',
            'restore_items',
            'view_delete_items',
            'force_delete_items',

            'view_roles',
            'create_roles',
            'edit_roles',
            'update_roles',
            'delete_roles',
            'restore_roles',
            'view_delete_roles',
            'force_delete_roles',

            'view_permissions',
            'create_permissions',
            'edit_permissions',
            'update_permissions',
            'delete_permissions',
            'restore_permisssions',
            'view_delete_permissions',
            'force_delete_permissions',

            'view_users',
            'create_users',
            'edit_users',
            'update_users',
            'delete_users',
            'restore_users',
            'view_delete_users',
            'force_delete_users',

            'view_log',
            'restore_log_items'
        ];
    }

    public static function alls(): array{
        return self::defaultPermissions();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
