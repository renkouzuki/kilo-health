<?php

namespace App\Repositories\Permissions;

use App\Models\Permission;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class PermissionController implements PermissionInterface
{
    public function getPermissions(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return Permission::query()
                ->when(
                    $search ?? null,
                    fn($query, $search) =>
                    $query->where(
                        fn($q) =>
                        $q->where('name', 'LIKE', "%{$search}%")
                    )
                )->latest()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving permissions: ' . $e->getMessage());
            throw new Exception('Error retrieving permissions');
        }
    }

    public function getPermissionsId(int $id): ?Permission
    {
        try {
            return Permission::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new Exception('Permission not found');
        } catch (Exception $e) {
            Log::error('Error retrieving permission: ' . $e->getMessage());
            throw new Exception('Error retrieving permission');
        }
    }

    public function createPermission(array $data): Permission
    {
        try {
            $permission = Permission::create($data);
            return $permission;
        } catch (Exception $e) {
            Log::error('Error creating permission: ' . $e->getMessage());
            throw new Exception('Error creating permission');
        }
    }

    public function updatePermission(int $id, array $newDetails): bool
    {
        try {
            $permission = Permission::findOrFail($id);
            $updated = $permission->update($newDetails);
            return $updated;
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Error updating permission: ' . $e->getMessage());
            throw new Exception('Error updating permission');
        }
    }

    public function getTrashedPermissions(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return Permission::onlyTrashed()->when(
                $search ?? null,
                fn($query, $search) =>
                $query->where(
                    fn($q) =>
                    $q->where('name', 'LIKE', "%{$search}%")
                )
            )->latest()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving trashed permissions: ' . $e->getMessage());
            throw new Exception('Error retrieving trashed permissions');
        }
    }

    public function deletePermission(int $id): bool
    {
        try {
            $permission = Permission::findOrFail($id);
            $deleted = $permission->delete();
            return $deleted;
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Error deleting permission: ' . $e->getMessage());
            throw new Exception('Error deleting permission');
        }
    }

    public function restorePermission(int $id): bool
    {
        try {
            $restored = Permission::withTrashed()->findOrFail($id)->restore();
            return $restored;
        } catch (Exception $e) {
            Log::error('Error restoring permission: ' . $e->getMessage());
            throw new Exception('Error restoring permission');
        }
    }

    public function forceDeletePermission(int $id): bool
    {
        try {
            $forceDeleted = Permission::withTrashed()->findOrFail($id)->forceDelete();
            return $forceDeleted;
        } catch (Exception $e) {
            Log::error('Error force deleting permission: ' . $e->getMessage());
            throw new Exception('Error force deleting permission');
        }
    }
}
