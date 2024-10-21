<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostViews\post;
use App\Http\Resources\PostViews\user;
use App\pagination\paginating;
use App\Repositories\PostViews\PostViewInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostViewController extends Controller
{
    private Request $req;

    protected $Repository;

    protected $pagination;

    public function __construct(PostViewInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
        $this->pagination = new paginating();
    }

    public function recordView(int $postId): JsonResponse
    {
        try {
            $userId = $this->req->user()->id;
            $this->Repository->recordView($postId, $userId);
            return response()->json(['success' => true, 'message' => 'View recorded successfully'], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getViewCount(int $postId): JsonResponse
    {
        try {
            $count = $this->Repository->getViewCount($postId);
            return response()->json(['success' => true, 'message' => 'Successfully get view count', 'view_count' => $count], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get view count', 'err' => $e->getMessage()], 500);
        }
    }

    public function getViewsByPost(int $postId): JsonResponse
    {
        try {
            $search = $this->req->search;
            $perPage = $this->req->per_page ?? 10;
            $views = $this->Repository->getViewsByPost($postId , $search , $perPage);
            return response()->json([
                'success' => true, 
                'message' => 'Successfully get views', 
                'data' => post::collection($views),
                'meta' => $this->pagination->metadata($views)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getViewsByUser(int $userId): JsonResponse
    {
        try {
            $search = $this->req->search;
            $perPage = $this->req->per_page ?? 10;
            $views = $this->Repository->getViewsByUser($userId , $search , $perPage);
            return response()->json([
                'success' => true, 
                'message' => 'Successfully get view by user', 
                'data' => user::collection($views),
                'meta' => $this->pagination->metadata($views)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function checkUserViewedPost(int $postId): JsonResponse
    {
        try {
            $userId = $this->req->user()->id;
            $hasViewed = $this->Repository->hasUserViewedPost($postId, $userId);
            return response()->json(['success' => true, 'message' => 'Successfully check if user has viewed post', 'has_viewed' => $hasViewed], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
