<?php

namespace App\Repositories\PostViews;

use App\Models\post_view;

class PostViewController {
    public function all(): post_view
    {
        $category = post_view::all()->latest();

        return $category;
    }

    public function find(int $id): post_view
    {
        return post_view::all()->latest();
    }

    public function create(array $data): void {}

    public function update($id, array $data): void {}

    public function delete($id): void {}
}
