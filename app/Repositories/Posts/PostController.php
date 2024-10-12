<?php

namespace App\Repositories\Posts;

use App\Models\post;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostController implements PostInterface
{

    protected $logService;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
    }

    public function getAllPosts(Request $req, int $perPage): LengthAwarePaginator
    {
        try {
            $filters = $req->only(['search', 'category_id', 'author_id']);

            $query = post::with(['category', 'author'])
                ->when($filters['search'] ?? null, function ($query, $search) {
                    return $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                })
                ->when($filters['category_id'] ?? null, function ($query, $categoryId) {
                    return $query->where('category_id', $categoryId);
                })
                ->when($filters['author_id'] ?? null, function ($query, $authorId) {
                    return $query->where('author_id', $authorId);
                })
                ->orderBy('created_at', 'desc');

            return $query->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving posts: ' . $e->getMessage());
            throw new Exception('Error retrieving posts');
        }
    }

    public function getPostById(int $id): ?Post
    {
        try {
            return post::with(['category', 'author'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return null;
        } catch (Exception $e) {
            Log::error('Error retrieving post: ' . $e->getMessage());
            throw new Exception('Error retrieving post');
        }
    }

    public function createPost(array $postData): Post
    {
        try {
            return DB::transaction(function () use ($postData) {
                $postData['read_time'] = $this->calculateReadTime($postData['description']);

                $post = post::create($postData);
                $this->logService->log(Auth::id(), 'created_post', post::class, $post->id, json_encode($postData));
                return $post;
            });
        } catch (Exception $e) {
            Log::error('Error creating post: ' . $e->getMessage());
            throw new Exception('Error creating post');
        }
    }

    public function updatePost(int $id, array $postData): bool
    {
        try {
            return DB::transaction(function () use ($id, $postData) {
                $post = post::findOrFail($id);

                if (isset($postData['description'])) {
                    $postData['read_time'] = $this->calculateReadTime($postData['description']);
                }

                $updated = $post->update($postData);
                if ($updated) {
                    $this->logService->log(Auth::id(), 'updated_post', post::class, $id, json_encode($postData));
                }
                return $updated;
            });
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Error updating post: ' . $e->getMessage());
            throw new Exception('Error updating post');
        }
    }

    public function deletePost(int $id): bool
    {
        try {
            $post = post::findOrFail($id);
            $deleted = $post->delete();
            if ($deleted) {
                $this->logService->log(Auth::id(), 'deleted_post', post::class, $id, null);
            }
            return $deleted;
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Error deleting post: ' . $e->getMessage());
            throw new Exception('Error deleting post');
        }
    }

    public function getPostsByCategory(int $categoryId, int $perPage): LengthAwarePaginator
    {
        try {
            return post::where('category_id', $categoryId)
                ->with(['author'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving posts by category: ' . $e->getMessage());
            throw new Exception('Error retrieving posts by category');
        }
    }

    public function getPostsByAuthor(int $authorId, int $perPage): LengthAwarePaginator
    {
        try {
            return post::where('author_id', $authorId)
                ->with(['category'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving posts by author: ' . $e->getMessage());
            throw new Exception('Error retrieving posts by author');
        }
    }

    public function incrementViews(int $postId): bool
    {
        try {
            return post::where('id', $postId)->increment('views') > 0;
        } catch (Exception $e) {
            Log::error('Error incrementing post views: ' . $e->getMessage());
            throw new Exception('Error incrementing post views');
        }
    }

    public function addLike(int $postId, int $userId): bool
    {
        try {
            $post = post::findOrFail($postId);
            $like = $post->likes()->where('user_id', $userId)->first();

            if (!$like) {
                $post->likes()->create(['user_id' => $userId]);
                $post->increment('likes');
                return true;
            }

            return false;
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Error adding like to post: ' . $e->getMessage());
            throw new Exception('Error adding like to post');
        }
    }

    public function removeLike(int $postId, int $userId): bool
    {
        try {
            $post = post::findOrFail($postId);
            $like = $post->likes()->where('user_id', $userId)->first();

            if ($like) {
                $like->delete();
                $post->decrement('likes');
                return true;
            }

            return false;
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Error removing like from post: ' . $e->getMessage());
            throw new Exception('Error removing like from post');
        }
    }

    public function getLikesCount(int $postId): int
    {
        try {
            return post::findOrFail($postId)->likes;
        } catch (ModelNotFoundException $e) {
            return 0;
        } catch (Exception $e) {
            Log::error('Error getting likes count for post: ' . $e->getMessage());
            throw new Exception('Error getting likes count for post');
        }
    }

    public function publishPost(int $postId): bool
    {
        try {
            $post = post::findOrFail($postId);
            $published = $post->update(['published_at' => Carbon::now()]);
            if ($published) {
                $this->logService->log(Auth::id(), 'published_post', post::class, $postId, json_encode(['published_at' => Carbon::now()]));
            }
            return $published;
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Error publishing post: ' . $e->getMessage());
            throw new Exception('Error publishing post');
        }
    }

    public function unpublishPost(int $postId): bool
    {
        try {
            $post = post::findOrFail($postId);
            $unpublished = $post->update(['published_at' => null]);
            if ($unpublished) {
                $this->logService->log(Auth::id(), 'unpublished_post', post::class, $postId, null);
            }
            return $unpublished;
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Error unpublishing post: ' . $e->getMessage());
            throw new Exception('Error unpublishing post');
        }
    }

    public function getPublishedPosts(Request $req): LengthAwarePaginator
    {
        try {
            $query = post::query()
                ->whereNotNull('published_at')
                ->where('published_at', '<=', Carbon::now())
                ->with(['category', 'author']);

            $this->applyFilters($query, $req);
            $this->applySorting($query, $req);

            return $query->paginate($req->per_page ?? 10);
        } catch (Exception $e) {
            Log::error('Error retrieving published posts: ' . $e->getMessage());
            throw new Exception('Error retrieving published posts');
        }
    }

    public function getTrashedPosts(int $perPage): LengthAwarePaginator
    {
        try {
            return post::onlyTrashed()
                ->with(['category', 'author'])
                ->orderBy('deleted_at', 'desc')
                ->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error retrieving trashed posts: ' . $e->getMessage());
            throw new Exception('Error retrieving trashed posts');
        }
    }

    public function restorePost(int $postId): bool
    {
        try {
            $restored = post::withTrashed()->findOrFail($postId)->restore();
            if ($restored) {
                $this->logService->log(Auth::id(), 'restored_post', post::class, $postId, null);
            }
            return $restored;
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Error restoring post: ' . $e->getMessage());
            throw new Exception('Error restoring post');
        }
    }

    public function forceDeletePost(int $postId): bool
    {
        try {
            $forceDeleted = post::withTrashed()->findOrFail($postId)->forceDelete();
            if ($forceDeleted) {
                $this->logService->log(Auth::id(), 'force_deleted_post', post::class, $postId, null);
            }
            return $forceDeleted;
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            Log::error('Error force deleting post: ' . $e->getMessage());
            throw new Exception('Error force deleting post');
        }
    }


    private function applyFilters(Builder $query, Request $req): void
    {
        $filters = [
            'search' => fn($value) => $query->where(function ($q) use ($value) {
                $q->where('title', 'like', "%{$value}%")
                    ->orWhere('description', 'like', "%{$value}%");
            }),
            'category_id' => fn($value) => $query->where('category_id', $value),
            'author_id' => fn($value) => $query->where('author_id', $value),
            'date_from' => fn($value) => $query->where('published_at', '>=', Carbon::parse($value)->startOfDay()),
            'date_to' => fn($value) => $query->where('published_at', '<=', Carbon::parse($value)->endOfDay()),
            'min_views' => fn($value) => $query->where('views', '>=', $value),
            'min_likes' => fn($value) => $query->where('likes', '>=', $value),
            'min_read_time' => fn($value) => $query->where('read_time', '>=', $value),
            'max_read_time' => fn($value) => $query->where('read_time', '<=', $value),
        ];

        foreach ($filters as $key => $callback) {
            $value = $req->input($key);
            if ($value !== null) {
                $callback($value);
            }
        }
    }

    private function applySorting(Builder $query, Request $req): void
    {
        $sortField = $req->input('sort_by', 'published_at');
        $sortDirection = $req->input('sort_direction', 'desc');

        $allowedSortFields = ['published_at', 'views', 'likes', 'read_time'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('published_at', 'desc');
        }
    }

    private function calculateReadTime(string $content): int
    {
        $wordsPerMinute = 200;
        $wordCount = str_word_count(strip_tags($content));
        return max(1, ceil($wordCount / $wordsPerMinute));
    }
}
