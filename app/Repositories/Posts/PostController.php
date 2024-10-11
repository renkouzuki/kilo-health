<?php

namespace App\Repositories\Posts;

use App\Models\post;
use Illuminate\Pagination\LengthAwarePaginator;

class PostController implements PostInterface {
    
    public function getAllPosts(array $filters, int $perPage): LengthAwarePaginator
    {
        
    }

    public function getPostById(int $id): ?post
    {
        
    }

    public function createPost(array $postData): post
    {
        
    }

    public function updatePost(int $id, array $postData): bool
    {
        
    }

    public function deletePost(int $id): bool
    {
        
    }

    public function getPostsByCategory(int $categoryId, int $perPage): LengthAwarePaginator
    {
        
    }

    public function getPostsByAuthor(int $authorId, int $perPage): LengthAwarePaginator
    {
        
    }

    public function incrementViews(int $postId): bool
    {
        
    }

    public function incrementLikes(int $postId): bool
    {
        
    }
}
