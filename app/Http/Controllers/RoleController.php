<?php

namespace App\Http\Controllers;

use App\Repositories\Roles\RoleInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(RoleInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

    public function index(): JsonResponse
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $roles = $this->Repository->getRoles($search, $perPage);
            return response()->json(['success' => true, 'message' => 'Successfully retrieving roles data',  'data' => $roles], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving roles', 'err' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->Repository->getRoleById($id);
            if (!$role) {
                return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully retrieving role', 'data' => $role], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving role', 'err' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'required|string|max:255',
            ]);
            $role = $this->Repository->createRole($validatedData);
            return response()->json(['success' => true, 'message' => 'Successfully store role', 'data' => $role], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error creating role', 'err' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'sometimes|string|max:255'
            ]);
            $updated = $this->Repository->updateRole($id, $validatedData);
            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully updated role'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating role', 'err' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $deleted = $this->Repository->deleteRole($id);
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Role not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully delete role'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleted role', 'err' => $e->getMessage()], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $restored = $this->Repository->restoreRole($id);
            if (!$restored) {
                return response()->json(['success' => false, 'message' => 'Role not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully restore role data'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error to restore data from role', 'err' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $deleted = $this->Repository->forceDeleteRole($id);
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Role not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully permenatly delete role data'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error trying to delete role permenantly', 'err' => $e->getMessage()], 500);
        }
    }

    public function displayTrashed()
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $trashedRoles = $this->Repository->getTrashedRoles($search, $perPage);
            return response()->json(['success' => true, 'message' => 'Successfuly retrieving soft delete roles', 'data' => $trashedRoles], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error trying to retrieving all the soft delete data from roles', 'err' => $e->getMessage()], 500);
        }
    }
}
