<?php

namespace App\Repositories;

use App\Models\AuditLogs;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController implements UserInterface
{

    protected $logService;

    public function __construct()
    {
        $this->logService = new AuditLogService();
    }

    public function getUsers(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return User::with(['role:id,name', 'role.permissions:id,name'])
            ->when(
                $filters['search'] ?? null,
                fn($query, $search) =>
                $query->where(
                    fn($q) =>
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

            $this->logService->log(Auth::id(), 'updated_permissions', Role::class, $role->id, json_encode(['permissions' => $permissions]));

            return $role->load('permissions');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRole(User $user, int $roleId): User
    {
        DB::beginTransaction();

        try {
            $user->role()->associate($roleId);
            $user->save();

            DB::commit();

            $this->logService->log(Auth::id(), 'updated_role', User::class, $user->id, json_encode(['role_id' => $roleId]));

            return $user->load('role');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function softDelete(int $userId): void
    {
        try {
            $user = User::findOrFail($userId);
            $user->delete();

            $this->logService->log(Auth::id(), 'soft_deleted', User::class, $user->id);
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

            $this->logService->log(Auth::id(), 'restored', User::class, $user->id);
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

            $this->logService->log(Auth::id(), 'permanently_deleted', User::class, $user->id);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to permanently delete');
        }
    }

    public function editUserInfo(Request $req): User
    {
        try {
            $data = [
                'name' => $req->name,
                'email' => $req->email,
                'password' => $req->filled('password') ? Hash::make($req->password) : null,
                'avatar' => $req->hasFile('avatar') ? $req->file('avatar')->store('avatars', 'public') : null,
            ];
            $data = array_filter($data);
            $user = User::findOrFail($req->user()->id)->update($data);
            return $user;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Failed to permanently delete');
        }
    }

    public function getAuditLogs(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return AuditLogs::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
