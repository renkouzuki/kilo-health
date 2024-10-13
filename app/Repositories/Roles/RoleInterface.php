<?php

namespace App\Repositories\Roles;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoleInterface
{
    public function getRoles(string $search = null, int $perPage = 10): LengthAwarePaginator;
    public function getRoleById(int $id): ? Role;
    public function createRole(array $data): Role;
    public function updateRole(int $id , array $newsDetails):bool;
    public function deleteRole(int $id): bool;
    public function restoreRole(int $id): bool;
    public function forceDeleteRole(int $id): bool;
    public function getTrashedRoles(string $search = null , int $perPage = 10): LengthAwarePaginator;
}
