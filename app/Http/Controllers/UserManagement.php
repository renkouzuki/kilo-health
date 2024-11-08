<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetAuditLogsRequest;
use App\Http\Resources\anotheruser;
use App\Http\Resources\auditlog;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\pagination\paginating;
use App\Repositories\User\UserInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserManagement extends Controller
{
    private Request $req;

    protected $Repository;
    protected $pagination;

    public function __construct(UserInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
        $this->pagination = new paginating();
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
                'data' => anotheruser::collection($users),
                'meta' => $this->pagination->metadata($users)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function GetUserDetails(User $user): JsonResponse
    {
        try {
            $user = $this->Repository->getDetails($user, true);
            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'users' => new UserResource($user)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function UpdateRolePermissions(int $id)
    {

        $this->req->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'int|exists:permissions,id',
        ]);

        try {
            $role = $this->Repository->updatePermissions($id, $this->req->permissions);
            return response()->json([
                'success' => true,
                'message' => 'Role permissions updated successfully',
                'role' => $role,
            ], 200);
        } catch(ValidationException $e){
            return response()->json(['success' => false , 'message' => $e->getMessage() , 'errors' => $e->errors()] , 422); 
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function UpdateUserRole(int $id)
    {

        $this->req->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        try {

            $user = $this->Repository->updateRole($id, $this->req->role_id);

            return response()->json([
                'success' => true,
                'message' => 'User role updated successfully',
                'user' => $user,
            ], 200);
        } catch(ValidationException $e){
            return response()->json(['success' => false , 'message' => $e->getMessage() , 'errors' => $e->errors()] , 422); 
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function rollbackDelete(int $userId): JsonResponse
    {
        try {
            $this->Repository->rollbackDelete($userId);
            return response()->json(['success' => true, 'message' => 'User restored successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function SoftDeleteUser(int $userId)
    {
        try {
            $this->Repository->softDelete($userId);
            return response()->json(['success' => true, 'message' => 'User soft deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function ShowTrashUsers(): JsonResponse
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $data = $this->Repository->getTrash($search, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Soft deleted users retrieved successfully',
                'data' => anotheruser::collection($data),
                'meta' => $this->pagination->metadata($data)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function RestoreUser(int $userId)
    {
        try {
            $this->Repository->restore($userId);
            return response()->json(['success' => true, 'message' => 'User restored successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function ForceDeleteUser(int $userId)
    {
        try {
            $this->Repository->forceDelete($userId);
            return response()->json(['success' => true, 'message' => 'User permanently deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAuditLog(int $userId): JsonResponse
    {
        $perPage = $this->req->per_page ?? 10;
        try {
            $users = $this->Repository->getAuditLogs($userId, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Auditlog retrieved successfully',
                'data' => auditlog::collection($users),
                'meta' => $this->pagination->metadata($users)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function adminUpdateUser(int $userId): JsonResponse
    {
        try {
            $this->req->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email',
                'password' => 'sometimes|string|min:8',
                'avatar' => 'sometimes|file|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            $result = $this->Repository->adminUpdateUser($userId, $this->req);
            return response()->json(['success' => true, 'message' => 'User update successfully', 'data' => $result], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
