<?php

namespace App\Repositories\PostViews;

use App\Models\post_view;
use Illuminate\Database\Eloquent\Collection;

interface PostViewInterface
{
    public function recordView(int $postId, int $userId): post_view;
    public function getViewCount(int $postId): int;
    public function getViewsByPost(int $postId): Collection;
    public function getViewsByUser(int $userId): Collection;
    public function hasUserViewedPost(int $postId, int $userId): bool;
}
