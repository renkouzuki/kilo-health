<?php

namespace App\Repositories\User;

use App\Events\UserMangement\RolePermissionsUpdated;
use App\Events\UserMangement\UserForceDeleted;
use App\Events\UserMangement\UserInfoUpdated;
use App\Events\UserMangement\UserRestored;
use App\Events\UserMangement\UserRoleUpdated;
use App\Events\UserMangement\UserSoftDeleted;
use App\Models\AuditLogs;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController implements UserInterface
{

    protected $logService;

    public function __construct()
    {
        $this->logService = new AuditLogService();
    }

    public function getUsers(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return User::with(['role:id,name'])
                ->when(
                    $search ?? null,
                    fn($query, $search) =>
                    $query->where(
                        fn($q) =>
                        $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                    )
                )
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving users: ' . $e->getMessage());
            throw new Exception('Error retrieving users');
        }
    }

    public function getDetails(User $user): User
    {
        try {
            return $user->load(['role:id,name', 'role.permissions:id,name']);
        } catch (ModelNotFoundException $e) {
            throw new Exception('User not found');
        } catch (Exception $e) {
            Log::error('Error retrieving user details: ' . $e->getMessage());
            throw new Exception('Error retrieving user details');
        }
    }

    public function updatePermissions(Role $role, array $permissions): Role
    {
        DB::beginTransaction();
        try {
            $role->permissions()->sync($permissions);
            DB::commit();

            $this->logService->log(Auth::id(), 'updated_permissions', Role::class, $role->id, json_encode(['permissions' => $permissions]));

            event(new RolePermissionsUpdated($role));
            return $role->load('permissions');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating permissions: ' . $e->getMessage());
            throw new Exception('Error updating permissions');
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

            event(new UserRoleUpdated($user));

            return $user->load('role');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating user role: ' . $e->getMessage());
            throw new Exception('Error updating user role');
        }
    }

    public function softDelete(int $userId): void
    {
        try {
            $user = User::findOrFail($userId);
            $user->delete();

            $this->logService->log(Auth::id(), 'soft_deleted', User::class, $user->id);

            event(new UserSoftDeleted($userId));
        } catch (ModelNotFoundException $e) {
            throw new Exception('User not found');
        } catch (Exception $e) {
            Log::error('Error soft deleting user: ' . $e->getMessage());
            throw new Exception('Error soft deleting user');
        }
    }

    public function getTrash(int $perPage): LengthAwarePaginator
    {
        try {
            return User::onlyTrashed()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving trashed users: ' . $e->getMessage());
            throw new Exception('Error retrieving trashed users');
        }
    }

    public function restore(int $userId): void
    {
        try {
            $user = User::withTrashed()->findOrFail($userId);
            $user->restore();

            $this->logService->log(Auth::id(), 'restored', User::class, $user->id);

            event(new UserRestored($user));
        } catch (ModelNotFoundException $e) {
            throw new Exception('User not found');
        } catch (Exception $e) {
            Log::error('Error restoring user: ' . $e->getMessage());
            throw new Exception('Error restoring user');
        }
    }

    public function forceDelete(int $userId): void
    {
        try {
            $user = User::withTrashed()->findOrFail($userId);
            $user->forceDelete();

            $this->logService->log(Auth::id(), 'permanently_deleted', User::class, $user->id);

            event(new UserForceDeleted($userId));
        } catch (ModelNotFoundException $e) {
            throw new Exception('User not found');
        } catch (Exception $e) {
            Log::error('Error permanently deleting user: ' . $e->getMessage());
            throw new Exception('Error permanently deleting user');
        }
    }

    public function editUserInfo(Request $req): User
    {
        try {
            $user = User::findOrFail($req->user()->id);
            $data = [
                'name' => $req->name,
                'email' => $req->email,
                'password' => $req->filled('password') ? Hash::make($req->password) : null
            ];

            if ($req->hasFile('avatar')) {
                if ($user->avatar) {
                    Storage::disk('s3')->delete($user->avatar);
                }

                $data['avatar'] = $req->file('avatar')->store('avatar', 's3');
            }

            $data = array_filter($data);

            $user->update($data);

            event(new UserInfoUpdated($user));
            return $user;
        } catch (ModelNotFoundException $e) {
            throw new Exception('User not found');
        } catch (Exception $e) {
            Log::error('Error editing user info: ' . $e->getMessage());
            throw new Exception('Error editing user info');
        }
    }

    public function getAuditLogs(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return AuditLogs::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving audit logs: ' . $e->getMessage());
            throw new Exception('Error retrieving audit logs');
        }
    }
}
