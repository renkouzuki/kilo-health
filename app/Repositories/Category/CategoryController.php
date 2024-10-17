<?php

namespace App\Repositories\Category;

use App\Events\Categories\CategoryCreated;
use App\Events\Categories\CategoryDeleted;
use App\Events\Categories\CategoryUpdated;
use App\Models\categorie;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategoryController implements CategoryInterface
{

    protected $logService;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
    }

    public function getAllCategories(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return categorie::query()
                ->when(
                    $search ?? null,
                    fn($query, $search) =>
                    $query->where(
                        fn($q) =>
                        $q->where('name', 'LIKE', "%{$search}%")
                    )
                )
                ->latest()->paginate($perPage);
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
            throw new Exception('Category not found');
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving category');
        }
    }

    public function createCategory(array $categoryDetails): categorie
    {
        try {
            if (isset($categoryDetails['icon']) && $categoryDetails['icon'] instanceof UploadedFile) {
                $logoPath = $categoryDetails['icon']->store('categories', 's3');
                $validatedData['icon'] = $logoPath;
            }
            $category = categorie::create($categoryDetails);
            $this->logService->log(Auth::id(), 'created_category', categorie::class, $category->id, json_encode($categoryDetails));
            event(new CategoryCreated($category->id));
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
            if (isset($newDetails['icon']) && $newDetails['icon'] instanceof UploadedFile) {
                if ($category->logo) {
                    Storage::disk('icon')->delete($category->icon);
                }

                $logoPath = $newDetails['icon']->store('categories', 's3');
                $newDetails['icon'] = $logoPath;
            }
            $updated = $category->update($newDetails);
            if ($updated) {
                $this->logService->log(Auth::id(), 'updated_category', categorie::class, $id, json_encode($newDetails));
                event(new CategoryUpdated($category));
            }
            return $updated;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Category not found');
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
            throw new Exception('Category not found');
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
                event(new CategoryDeleted($id));
            }
            return $deleted;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Category not found');
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
        } catch (ModelNotFoundException $e) {
            throw new Exception('Category not found');
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
        } catch (ModelNotFoundException $e) {
            throw new Exception('Category not found');
        } catch (Exception $e) {
            Log::error('Error force deleting category: ' . $e->getMessage());
            throw new Exception('Error force deleting category');
        }
    }

    public function getTrashedCategories(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return categorie::onlyTrashed()->when(
                $search ?? null,
                fn($query, $search) =>
                $query->where(
                    fn($q) =>
                    $q->where('name', 'LIKE', "%{$search}%")
                )
            )->latest()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving trashed categories: ' . $e->getMessage());
            throw new Exception('Error retrieving trashed categories');
        }
    }
}
