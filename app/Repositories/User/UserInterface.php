<?php

namespace App\Repositories\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface UserInterface {
    public function getUsers(string $search = null, int $perPage = 10):LengthAwarePaginator;
    public function updatePermissions(int $id, array $permissions): Role;
    public function updateRole(int $id, int $roleId):User;
    public function getDetails(User $user):User;
    public function softDelete(int $userId): void;
    public function getTrash(string $search = null , int $perPage = 10): LengthAwarePaginator;
    public function restore(int $userId): void;
    public function forceDelete(int $userId): void;
    public function adminUpdateUser(int $userId , Request $req): User;
    public function getAuditLogs(int $userId, int $perPage = 10): LengthAwarePaginator;
    public function rollbackDelete(int $logId): bool;
}