<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public static function defaultPermissions(): array {
        return [
            'view_items',
            'create_items',
            'edit_items',
            'delete_items',
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
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
