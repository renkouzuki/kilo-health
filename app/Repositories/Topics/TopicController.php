<?php

namespace App\Repositories\Topics;

use App\Models\topic;

class TopicController {
    public function all(): topic
    {
        $category = topic::all()->latest();

        return $category;
    }

    public function find(int $id): topic
    {
        return topic::all()->latest();
    }

    public function create(array $data): void {}

    public function update($id, array $data): void {}

    public function delete($id): void {}
}
