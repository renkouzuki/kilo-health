<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class RepositoryController implements RepositoryInterface {
    public function getUsers(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return User::with(['role:id,name', 'role.permissions:id,name'])
            ->when($filters['search'] ?? null, fn($query, $search) => 
                $query->where(fn($q) => 
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                )
            )
            ->paginate($perPage);
    }

    public function getDetails(User $user): User
    {
        try {
            $user->load(['role:id,name', 'role.permissions:id,name']);
            return $user;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to soft delete');
        }
    }

    public function updatePermissions(Role $role, array $permissions): Role
    {
        DB::beginTransaction();

        try {
            $role->permissions()->sync($permissions);
            DB::commit();
            return $role->load('permissions');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRole(User $user, int $roleId): User
    {
        DB::beginTransaction();

        try{
            $user->role()->associate($roleId);
            $user->save();

            DB::commit();
            return $user->load('role');
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;  
        }
    }

    public function softDelete(int $userId): void
    {
        try {
            $user = User::findOrFail($userId);
            $user->delete();
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to soft delete');
        }
    }

    public function getTrash(int $perPage): LengthAwarePaginator
    {
        $user = User::onlyTrashed()->paginate($perPage);
        return $user;
    }

    public function restore(int $userId): void
    {
        try {
            $user = User::withTrashed()->findOrFail($userId);
            $user->restore();
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to restore');
        }
    }

    public function forceDelete(int $userId): void
    {
        try {
            $user = User::withTrashed()->findOrFail($userId);
            $user->forceDelete();
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to permanently delete');
        }
    }
}