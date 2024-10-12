<?php

namespace App\Repositories\PostViews;

use App\Models\post;
use App\Models\post_view;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostViewController implements PostViewInterface
{
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
                }

                $postView->viewed_at = now();
                $postView->save();

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

    public function getViewsByPost(int $postId): Collection
    {
        try {
            return post_view::where('post_id', $postId)
                ->with('user:id,name')
                ->orderBy('viewed_at', 'desc')
                ->get();
        } catch (Exception $e) {
            Log::error('Error getting views by post: ' . $e->getMessage());
            throw new Exception('Error getting views by post');
        }
    }

    public function getViewsByUser(int $userId): Collection
    {
        try {
            return post_view::where('user_id', $userId)
                ->with('post:id,title')
                ->orderBy('viewed_at', 'desc')
                ->get();
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
