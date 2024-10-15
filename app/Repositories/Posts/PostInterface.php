<?php

namespace App\Repositories\Posts;

use App\Models\post;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostInterface
{
    public function getAllPosts(Request $req, int $perPage): LengthAwarePaginator;
    public function getPostById(int $id): ? post;
    public function createPost(array $postData): post;
    public function updatePost(int $id, array $postData): bool;
    public function deletePost(int $id): bool;
    public function getPostsByCategory(int $categoryId, int $perPage): LengthAwarePaginator;
    public function getPostsByAuthor(int $authorId, int $perPage): LengthAwarePaginator;
    public function incrementViews(int $postId): bool;
    public function addLike(int $postId, int $userId): bool;
    public function removeLike(int $postId, int $userId): bool;
    public function getLikesCount(int $postId): int;
    public function publishPost(int $postId): bool;
    public function unpublishPost(int $postId): bool;
    public function getPublishedPosts(Request $req , int $perPage): LengthAwarePaginator;
    public function getTrashedPosts(Request $req , int $perPage): LengthAwarePaginator;
    public function restorePost(int $postId): bool;
    public function forceDeletePost(int $postId): bool;
}
