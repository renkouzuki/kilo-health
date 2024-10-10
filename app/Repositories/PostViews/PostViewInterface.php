<?php

namespace App\Repositories\PostViews;

use App\Models\post_view;

interface PostViewInterface
{
    public function all(): post_view;
    public function find(int $id): post_view;
    public function create(array $data): void;
    public function update(int $id, array $data): void;
    public function delete(int $id): void;
}
