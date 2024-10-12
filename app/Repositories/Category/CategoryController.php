<?php

namespace App\Repositories\Category;

use App\Models\categorie;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CategoryController implements CategoryInterface
{

    public function getAllCategories(): Collection
    {
        try {
            return categorie::all();
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving categories');
        }
    }

    public function getCategoryById(int $id): ?categorie
    {
        try {
            return categorie::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return null;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving category');
        }
    }

    public function createCategory(array $categoryDetails): categorie
    {
        try {
            return categorie::create($categoryDetails);
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error creating category');
        }
    }

    public function updateCategory(int $id, array $newDetails): bool
    {
        try {
            $category = categorie::findOrFail($id);
            return $category->update($newDetails);
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error updating category');
        }
    }

    public function getCategoryBySlug(string $slug): ?categorie
    {
        try {
            return categorie::where('slug', $slug)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return null;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving category by slug');
        }
    }

    public function deleteCategory(int $id): bool
    {
        try {
            $category = categorie::findOrFail($id);
            return $category->delete();
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error deleting category');
        }
    }

    public function restoreCategory(int $id): bool
    {
        try {
            return categorie::withTrashed()->findOrFail($id)->restore();
        } catch (Exception $e) {
            Log::error('Error restoring category: ' . $e->getMessage());
            throw new Exception('Error restoring category');
        }
    }

    public function forceDeleteCategory(int $id): bool
    {
        try {
            return categorie::withTrashed()->findOrFail($id)->forceDelete();
        } catch (Exception $e) {
            Log::error('Error force deleting category: ' . $e->getMessage());
            throw new Exception('Error force deleting category');
        }
    }

    public function getTrashedCategories(int $perPage): LengthAwarePaginator
    {
        try {
            return categorie::onlyTrashed()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving trashed categories: ' . $e->getMessage());
            throw new Exception('Error retrieving trashed categories');
        }
    }
}
