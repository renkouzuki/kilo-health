<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    public static function defaultPermissions(): array{
        return [
            'view_items',
            'create_items',
            'update_items',
            'edit_items',
            'delete_items',
            'view_roles',
            'create_roles',
            'update_roles',
            'edit_roles',
            'delete_roles',
        ];
    }
}
