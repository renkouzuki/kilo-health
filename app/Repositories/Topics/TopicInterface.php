<?php

namespace App\Repositories\Topics;

use App\Models\topic;
use Illuminate\Database\Eloquent\Collection;

interface TopicInterface {
    public function getAllTopics(): Collection;
    public function getTopicById(int $id): ? topic;
    public function deleteTopic(int $id): bool;
    public function createTopic(array $topicDetails): topic;
    public function updateTopic(int $id, array $newDetails): bool;
    public function getTopicsByCategory(int $categoryId): Collection;
}
