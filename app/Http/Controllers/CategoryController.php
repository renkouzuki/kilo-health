<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Repositories\Category\CategoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(CategoryInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

    public function index(): JsonResponse
    {
        try {
            $categories = $this->Repository->getAllCategories();
            return response()->json(['success' => true, 'message' => 'Successfully retrieving categories', 'data' => CategoryResource::collection($categories)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving categories', 'err' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'required|max:255',
                'icon' => 'required|max:500',
            ]);

            $validatedData['slug'] = Str::slug($validatedData['name']);

            $category = $this->Repository->createCategory($validatedData);
            return response()->json(['success' => true, 'message' => 'Successfully store category', 'data' => new CategoryResource($category)], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error creating category', 'err' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->Repository->getCategoryById($id);
            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully retrieving category', 'data' => new CategoryResource($category)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving category', 'err' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'name' => 'required|max:255',
                'icon' => 'required|max:500',
            ]);

            $validatedData['slug'] = Str::slug($validatedData['name']);

            $updated = $this->Repository->updateCategory($id, $validatedData);
            if (!$updated) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            $category = $this->Repository->getCategoryById($id);
            return response()->json(['success' => true, 'message' => 'Successfully updated category', 'data' => new CategoryResource($category)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating category', 'err' => $e->getMessage()], 500);
        }
    }

    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $category = $this->Repository->getCategoryBySlug($slug);
            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'successfully retrieved category', 'data' => new CategoryResource($category)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving category', 'err' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $deleted = $this->Repository->deleteCategory($id);
            if (!$deleted) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully deleted category'], 204);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting category', 'err' => $e->getMessage()], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $restored = $this->Repository->restoreCategory($id);
            if (!$restored) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Category restored successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error trying to restore category', 'err' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $deleted = $this->Repository->forceDeleteCategory($id);
            if (!$deleted) {
                return response()->json(['message' => 'Category not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'successfully permenantly deleted category'], 204);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error permenatly deleted category', 'err' => $e->getMessage()], 500);
        }
    }

    public function trashed(): JsonResponse
    {
        try {
            $trashedCategories = $this->Repository->getTrashedCategories($this->req->per_page ?? 10);
            return response()->json(['success' => true, 'message' => 'Successfully retrieving soft deleted categories', 'data' => CategoryResource::collection($trashedCategories)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error retrieving soft deleted categories', 'err' => $e->getMessage()], 500);
        }
    }
}
