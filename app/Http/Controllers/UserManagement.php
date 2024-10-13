<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetAuditLogsRequest;
use App\Http\Resources\anotheruser;
use App\Http\Resources\auditlogResource;
use App\Http\Resources\softdeleteuserCollection;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Repositories\User\UserInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserManagement extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(UserInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

    public function ShowAll(): JsonResponse
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $users = $this->Repository->getUsers($search, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'users' => anotheruser::collection($users)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update role permissions', 'err' => $e->getMessage()], 500);
        }
    }


    public function GetUserDetails(User $user): JsonResponse
    {
        try {
            $user = $this->Repository->getDetails($user,true);
            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'users' => new UserResource($user)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get user details', 'err' => $e->getMessage()], 500);
        }
    }

    public function UpdateRolePermissions(Role $role)
    {

        $this->req->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'int|exists:permissions,id',
        ]);

        try {
            $role = $this->Repository->updatePermissions($role, $this->req->permissions);
            return response()->json([
                'success' => true,
                'message' => 'Role permissions updated successfully',
                'role' => $role,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update role permissions', 'err' => $e->getMessage()], 500);
        }
    }

    public function UpdateUserRole(User $user)
    {

        $this->req->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        try {

            $user = $this->Repository->updateRole($user, $this->req->role_id);

            return response()->json([
                'success' => true,
                'message' => 'User role updated successfully',
                'user' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update user role', 'err' => $e->getMessage()], 500);
        }
    }

    public function SoftDeleteUser(int $userId)
    {
        try {
            $this->Repository->softDelete($userId);
            return response()->json(['success' => true, 'message' => 'User soft deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'User not found', 'err' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to soft delete user', 'err' => $e->getMessage()], 500);
        }
    }

    public function ShowTrashUsers(): JsonResponse
    {
        $perPage = $this->req->per_page ?? 10;
        try {
            $data = $this->Repository->getTrash($perPage);
            return response()->json(['success' => false, 'data' => new softdeleteuserCollection($data), 'message' => 'Soft deleted users retrieved successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get softdelete users', 'err' => $e->getMessage()], 500);
        }
    }

    public function RestoreUser(int $userId)
    {
        try {
            $this->Repository->restore($userId);
            return response()->json(['success' => true, 'message' => 'User restored successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to restore user', 'err' => $e->getMessage()], 500);
        }
    }

    public function ForceDeleteUser(int $userId)
    {
        try {
            $this->Repository->forceDelete($userId);
            return response()->json(['success' => true, 'message' => 'User permanently deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to  Force Delete user', 'err' => $e->getMessage()], 500);
        }
    }

    public function UpdateUserInfo()
    {
        try {
            $this->req->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email',
                'password' => 'sometimes|string|min:8|confirmed',
                'avatar' => 'sometimes|file|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            $result = $this->Repository->editUserInfo($this->req);
            return response()->json(['success' => true, 'message' => 'User update successfully', 'data' => $result], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to  update user', 'err' => $e->getMessage()], 500);
        }
    }

    public function getAuditLogs(GetAuditLogsRequest $req): JsonResponse
    {
        $userId = $req->user_id;
        $perPage = $this->req->per_page ?? 10;
        try {
            $users = $this->Repository->getAuditLogs($userId, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Auditlog retrieved successfully',
                'users' => auditlogResource::collection($users)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'something went wrong', 'err' => $e->getMessage()], 500);
        }
    }
}
