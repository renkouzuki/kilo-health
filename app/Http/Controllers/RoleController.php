<?php

namespace App\Http\Controllers;

use App\Http\Resources\roles;
use App\pagination\paginating;
use App\Repositories\Roles\RoleInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    private Request $req;

    protected $Repository;
    protected $pagination;

    public function __construct(RoleInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
        $this->pagination = new paginating();
    }

    public function index(): JsonResponse
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $roles = $this->Repository->getRoles($search, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieving roles data',
                'data' => roles::collection($roles),
                'meta' => $this->pagination->metadata($roles)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->Repository->getRoleById($id);
            return response()->json(['success' => true, 'message' => 'Successfully retrieving role', 'data' => $role], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
        } catch(ValidationException $e){
            return response()->json(['success' => false , 'message' => 'Oops look like a validation errors occurred' , 'errors' => $e->errors()] , 422); 
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'sometimes|string|max:255'
            ]);
            $this->Repository->updateRole($id, $validatedData);
            return response()->json(['success' => true, 'message' => 'Successfully updated role'], 200);
        } catch(ValidationException $e){
            return response()->json(['success' => false , 'message' => 'Oops look like a validation errors occurred' , 'errors' => $e->errors()] , 422); 
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating role', 'err' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->Repository->deleteRole($id);
            return response()->json(['success' => true, 'message' => 'Successfully delete role'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $this->Repository->restoreRole($id);
            return response()->json(['success' => true, 'message' => 'Successfully restore role data'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->Repository->forceDeleteRole($id);
            return response()->json(['success' => true, 'message' => 'Successfully permenatly delete role data'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function displayTrashed()
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $trashedRoles = $this->Repository->getTrashedRoles($search, $perPage);
            return response()->json([
                'success' => true, 
                'message' => 'Successfuly retrieving soft delete roles', 
                'data' => roles::collection($trashedRoles),
                'meta'=> $this->pagination->metadata($trashedRoles)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
