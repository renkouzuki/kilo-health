<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface UserInterface {
    public function getUsers(string $search = null, int $perPage = 10):LengthAwarePaginator;
    public function updatePermissions(Role $role, array $permissions): Role;
    public function updateRole(User $user , int $roleId):User;
    public function getDetails(User $user):User;
    public function softDelete(int $userId): void;
    public function getTrash(int $perPage): LengthAwarePaginator;
    public function restore(int $userId): void;
    public function forceDelete(int $userId): void;
    public function editUserInfo(Request $req):User;
    public function getAuditLogs(int $userId, int $perPage = 10): LengthAwarePaginator;
}