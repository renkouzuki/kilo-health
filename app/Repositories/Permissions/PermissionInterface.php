<?php

namespace App\Repositories\Permissions;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PermissionInterface
{
    public function getPermissions(string $search = null, int $perPage = 10): LengthAwarePaginator;
    public function getPermissionsId(int $id): ?Permission;
    public function createPermission(array $data): Permission;
    public function updatePermission(int $id, array $newDetails): bool;
    public function deletePermission(int $id): bool;
    public function restorePermission(int $id): bool;
    public function forceDeletePermission(int $id): bool;
    public function getTrashedPermissions(string $search = null, int $perPage = 10): LengthAwarePaginator;
}
