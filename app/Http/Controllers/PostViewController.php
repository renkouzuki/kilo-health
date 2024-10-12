<?php

namespace App\Http\Controllers;

use App\Repositories\PostViews\PostViewInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostViewController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(PostViewInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }
    
    public function recordView(int $postId): JsonResponse
    {
        try {
            $userId = $this->req->user()->id;
            $this->Repository->recordView($postId, $userId);
            return response()->json(['message' => 'View recorded successfully'], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getViewCount(int $postId): JsonResponse
    {
        try {
            $count = $this->Repository->getViewCount($postId);
            return response()->json(['view_count' => $count]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getViewsByPost(int $postId): JsonResponse
    {
        try {
            $views = $this->Repository->getViewsByPost($postId);
            return response()->json(['views' => $views]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getViewsByUser(int $userId): JsonResponse
    {
        try {
            $views = $this->Repository->getViewsByUser($userId);
            return response()->json(['views' => $views]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function checkUserViewedPost(int $postId): JsonResponse
    {
        try {
            $userId = $this->req->user()->id;
            $hasViewed = $this->Repository->hasUserViewedPost($postId, $userId);
            return response()->json(['has_viewed' => $hasViewed]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
