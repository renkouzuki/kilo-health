<?php

namespace App\Repositories\Category;

use App\Models\categorie;

class CategoryController implements CategoryInterface
{

    public function all(): categorie
    {
        $category = categorie::all()->latest();

        return $category;
    }

    public function find(int $id): categorie
    {
        return categorie::all()->latest();
    }

    public function create(array $data): void {}

    public function update($id, array $data): void {}

    public function delete($id): void {}
}
