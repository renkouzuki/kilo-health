<?php

namespace App\Repositories\Category;

use App\Models\categorie;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryInterface
{
    public function getAllCategories(string $search = null, int $perPage = 10): LengthAwarePaginator;
    public function getCategoryById(int $id): ?categorie;
    public function createCategory(array $categoryDetails): categorie;
    public function updateCategory(int $id, array $newDetails): bool;
    public function getCategoryBySlug(string $slug): ?categorie;

    public function deleteCategory(int $id): bool;
    public function restoreCategory(int $id): bool;
    public function forceDeleteCategory(int $id): bool;
    public function getTrashedCategories(string $search = null, int $perPage = 10): LengthAwarePaginator;
}
