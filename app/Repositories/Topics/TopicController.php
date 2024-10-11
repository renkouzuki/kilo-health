<?php

namespace App\Repositories\Topics;

use App\Models\topic;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class TopicController implements TopicInterface
{
    public function getAllTopics(): Collection
    {
        try {
            return topic::all();
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving topics');
        }
    }

    public function getTopicById(int $id): ?topic
    {
        try {
            return topic::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return null;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving topic');
        }
    }

    public function deleteTopic(int $id): bool
    {
        try {
            $topic = topic::findOrFail($id);
            return $topic->delete();
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error deleting topic');
        }
    }

    public function createTopic(array $topicDetails): topic
    {
        try {
            return topic::create($topicDetails);
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error creating topic');
        }
    }

    public function updateTopic(int $id, array $newDetails): bool
    {
        try {
            $topic = topic::findOrFail($id);
            return $topic->update($newDetails);
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error updating topic');
        }
    }

    public function getTopicsByCategory(int $categoryId): Collection
    {
        try {
            return topic::where('category_id', $categoryId)->get();
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving topics by category');
        }
    }
}
