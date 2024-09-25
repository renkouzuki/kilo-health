<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class gen_user extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::query()->delete();
        Role::query()->delete();

        foreach (Role::allRoles() as $roleName) {
            Role::create(['name' => $roleName]);
        }
        $this->command->info(" all the seeder roles that loop successfully insert into the database");

        foreach (Permission::defaultPermissions() as $permissionName) {
            Permission::create(['name' => $permissionName]);
        }
        $this->command->info(" all the seeder permissions that loop successfully insert into the database");

        foreach (Role::allRoles() as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $permissionName = Role::getPermissionsForRole($roleName);
            $permissionId = Permission::whereIn('name', $permissionName)->pluck('id')->toArray();
            $role->permissions()->sync($permissionId);
            $this->command->info("all seeders insert into role_permissions");
        }

        $users = [
            [
                "name" => "super_admin",
                "email" => "superadmin@gmail.com",
                "password" => "superadmin123",
                "role" => Role::SUPER_ADMIN
            ],
            [
                "name" => "admin",
                "email" => "admin@gmail.com",
                "password" => "admin123",
                "role" => Role::ADMIN
            ],
            [
                "name"=>"user",
                "email"=>"user@gmail.com",
                "password"=>"user123",
                "role"=>Role::USER
            ]
        ];

        foreach ($users as $user){
            $role = Role::where('name' , $user['role'])->first();

            if(!$role){
                $this->command->info("user roles not founded! 'user['role']'");
                continue;
            }

            User::updateOrCreate([
                'name'=>$user['name'],
                'email'=>$user['email'],
                'password'=>Hash::make($user['password']),
                'role_id'=>$role->id
            ]);

            $this->command->info("all roles are been inserted!");
        }
    }
}
