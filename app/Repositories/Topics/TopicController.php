<?php

namespace App\Repositories\Topics;

use App\Events\Topics\TopicCreated;
use App\Events\Topics\TopicDeleted;
use App\Events\Topics\TopicUpdated;
use App\Models\topic;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TopicController implements TopicInterface
{
    protected $logService;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
    }

    public function getAllTopics(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return topic::query()
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
            throw new Exception('Error retrieving topics');
        }
    }

    public function getTopicById(int $id): ?topic
    {
        try {
            return topic::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return throw new Exception('Topic not found');
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving topic');
        }
    }

    public function deleteTopic(int $id): bool
    {
        try {
            $topic = topic::findOrFail($id);
            $deleted = $topic->delete();
            if($deleted){
                event(new TopicDeleted($topic));
            }
            return $deleted;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Topic not found');
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error deleting topic');
        }
    }

    public function createTopic(array $topicDetails): topic
    {
        try {
            $topic = topic::create($topicDetails);
            event(new TopicCreated($topic));
            return $topic;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error creating topic');
        }
    }

    public function updateTopic(int $id, array $newDetails): bool
    {
        try {
            $topic = topic::findOrFail($id);
            $updated = $topic->update($newDetails);
            if($updated){
                event(new TopicUpdated($topic));
            }
            return $updated;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Topic not found');
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

    public function restoreTopic(int $id): bool
    {
        try {
            $restored = topic::withTrashed()->findOrFail($id)->restore();
            if ($restored) {
                $this->logService->log(Auth::id(), 'restored_topic', topic::class, $id, null);
            }
            return $restored;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Topic not found');
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error restoring topic');
        }
    }

    public function forceDeleteTopic(int $id): bool
    {
        try {
            $forceDeleted = topic::withTrashed()->findOrFail($id)->forceDelete();
            if ($forceDeleted) {
                $this->logService->log(Auth::id(), 'force_deleted_topic', topic::class, $id, null);
            }
            return $forceDeleted;
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error force deleting topic');
        }
    }

    public function getTrashedTopics(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return topic::onlyTrashed()->when(
                $search ?? null,
                fn($query, $search) =>
                $query->where(
                    fn($q) =>
                    $q->where('name', 'LIKE', "%{$search}%")
                )
            )->latest()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            throw new Exception('Error retrieving trashed topics');
        }
    }
}
