<?php

namespace App\Http\Controllers;

use App\Repositories\Permissions\PermissionInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PemrissionController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(PermissionInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

    public function index(): JsonResponse
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $permissions = $this->Repository->getPermissions($search, $perPage);
            return response()->json(['success' => true, 'message' => 'Successfully retrieving permissions', 'data' => $permissions], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving permissions', 'err' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $permission = $this->Repository->getPermissionsId($id);
            if (!$permission) {
                return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully retrieving permission', 'data' => $permission], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving permission', 'err' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'required|string|max:255',
            ]);
            $permission = $this->Repository->createPermission($validatedData);
            return response()->json(['success' => true, 'message' => 'Successfully store permission', 'data' => $permission], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error creating permission', 'err' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'sometimes|string|max:255'
            ]);
            $updated = $this->Repository->updatePermission($id, $validatedData);
            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully updated permission'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating permission', 'err' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $deleted = $this->Repository->deletePermission($id);
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully delete permission'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleted permission', 'err' => $e->getMessage()], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $restored = $this->Repository->restorePermission($id);
            if (!$restored) {
                return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully restore permission data'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error to restore data from permission', 'err' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $deleted = $this->Repository->forceDeletePermission($id);
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully permenatly delete permission data'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error trying to delete permission permenantly', 'err' => $e->getMessage()], 500);
        }
    }

    public function displayTrashed()
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $trashedPermissions = $this->Repository->getTrashedPermissions($search, $perPage);
            return response()->json(['success' => true, 'message' => 'Successfuly retrieving soft delete permission data', 'data' => $trashedPermissions], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error trying to retrieving all the soft delete data from permission', 'err' => $e->getMessage()], 500);
        }
    }
}
