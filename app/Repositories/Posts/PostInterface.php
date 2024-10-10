<?php

namespace App\Repositories\Posts;

use App\Models\post;

interface PostInterface
{
    public function all(): post;
    public function find(int $id): post;
    public function create(array $data): void;
    public function update(int $id, array $data): void;
    public function delete(int $id): void;
}
