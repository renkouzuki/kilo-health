<?php

namespace App\Repositories\PostViews;

use App\Events\Posts\PostViewed;
use App\Models\post;
use App\Models\post_view;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostViewController implements PostViewInterface
{

    protected $logService;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
    }

    public function recordView(int $postId, int $userId): post_view
    {
        try {
            return DB::transaction(function () use ($postId, $userId) {
                $post = post::findOrFail($postId);

                $postView = post_view::firstOrCreate([
                    'post_id' => $postId,
                    'user_id' => $userId,
                ]);

                if ($postView->wasRecentlyCreated) {
                    $post->increment('views');
                    $this->logService->log($userId, 'recorded_post_view', post_view::class, $postView->id, json_encode([
                        'post_id' => $postId,
                        'user_id' => $userId,
                        'viewed_at' => now(),
                    ]));
                }

                $postView->viewed_at = now();
                $postView->save();
                event(new PostViewed($post, $userId));
                return $postView;
            });
        } catch (Exception $e) {
            Log::error('Error recording post view: ' . $e->getMessage());
            throw new Exception('Error recording post view');
        }
    }

    public function getViewCount(int $postId): int
    {
        try {
            return post::findOrFail($postId)->views;
        } catch (Exception $e) {
            Log::error('Error getting view count: ' . $e->getMessage());
            throw new Exception('Error getting view count');
        }
    }

    public function getViewsByPost(int $postId, string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return post_view::where('post_id', $postId)
                ->with('user:id,name,avatar')->when(
                    $search ?? null,
                    fn($query, $search) =>
                    $query->whereHas(
                        'user',
                        fn($query) =>
                        $query->where('name', 'like', '%' . $search . '%')
                    )
                )->orderBy('viewed_at', 'desc')->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error getting views by post: ' . $e->getMessage());
            throw new Exception('Error getting views by post');
        }
    }

    public function getViewsByUser(int $userId, string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return post_view::where('user_id', $userId)
                ->with('post:id,title,description,thumbnail')
                ->when(
                    $search ?? null,
                    fn($query, $search) =>
                    $query->whereHas(
                        'post',
                        fn($query) =>
                        $query->where('title', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%')
                    )
                )->orderBy('viewed_at', 'desc')->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error getting views by user: ' . $e->getMessage());
            throw new Exception('Error getting views by user');
        }
    }

    public function hasUserViewedPost(int $postId, int $userId): bool
    {
        try {
            return post_view::where('post_id', $postId)
                ->where('user_id', $userId)
                ->exists();
        } catch (Exception $e) {
            Log::error('Error checking if user viewed post: ' . $e->getMessage());
            throw new Exception('Error checking if user viewed post');
        }
    }
}
