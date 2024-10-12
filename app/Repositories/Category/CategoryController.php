<?php

namespace App\Repositories\Category;

use App\Models\categorie;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CategoryController implements CategoryInterface
{

    protected $logService;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
    }

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
            $category = categorie::create($categoryDetails);
            $this->logService->log(Auth::id(), 'created_category', categorie::class, $category->id, json_encode($categoryDetails));
            return $category;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error creating category');
        }
    }

    public function updateCategory(int $id, array $newDetails): bool
    {
        try {
            $category = categorie::findOrFail($id);
            $updated = $category->update($newDetails);
            if ($updated) {
                $this->logService->log(Auth::id(), 'updated_category', categorie::class, $id, json_encode($newDetails));
            }
            return $updated;
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
            $deleted = $category->delete();
            if ($deleted) {
                $this->logService->log(Auth::id(), 'deleted_category', categorie::class, $id, null);
            }
            return $deleted;
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
            $restored = categorie::withTrashed()->findOrFail($id)->restore();
            if ($restored) {
                $this->logService->log(Auth::id(), 'restored_category', categorie::class, $id, null);
            }
            return $restored;
        } catch (Exception $e) {
            Log::error('Error restoring category: ' . $e->getMessage());
            throw new Exception('Error restoring category');
        }
    }

    public function forceDeleteCategory(int $id): bool
    {
        try {
            $forceDeleted = categorie::withTrashed()->findOrFail($id)->forceDelete();
            if ($forceDeleted) {
                $this->logService->log(Auth::id(), 'force_deleted_category', categorie::class, $id, null);
            }
            return $forceDeleted;
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
