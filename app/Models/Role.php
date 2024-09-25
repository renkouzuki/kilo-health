<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public const ADMIN = 'admin';
    public const SUPER_ADMIN = 'super_admin';
    public const USER = 'user';

    public static function allRoles(): array{
        return [
            self::ADMIN,
            self::SUPER_ADMIN,
            self::USER
        ];
    }

    public static function getPermissionsForRole($permissions){
        switch($permissions){
            case self::SUPER_ADMIN:
            case self::ADMIN:
                return Permission::pluck('name')->toArray(); Permission::all()->toArray();
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
