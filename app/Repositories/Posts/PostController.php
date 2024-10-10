<?php

namespace App\Repositories\Posts;

use App\Models\post;

class PostController {
    public function all(): post
    {
        $category = post::all()->latest();

        return $category;
    }

    public function find(int $id): post
    {
        return post::all()->latest();
    }

    public function create(array $data): void {}

    public function update($id, array $data): void {}

    public function delete($id): void {}
}
