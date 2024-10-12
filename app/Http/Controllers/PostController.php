<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Repositories\Posts\PostInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(PostInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }


    public function index(): AnonymousResourceCollection
    {
        try {
            $posts = $this->Repository->getAllPosts($this->req , 10);
            return PostResource::collection($posts);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'title' => 'required|max:255',
                'description' => 'required',
                'category_id' => 'required|exists:categories,id',
                'author_id' => 'required|exists:users,id',
                'thumbnail' => 'required|url',
            ]);

            $post = $this->Repository->createPost($validatedData);
            return response()->json(new PostResource($post), 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $post = $this->Repository->getPostById($id);
            if (!$post) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(new PostResource($post));
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'title' => 'sometimes|required|max:255',
                'description' => 'sometimes|required',
                'category_id' => 'sometimes|required|exists:categories,id',
                'thumbnail' => 'sometimes|required|url',
            ]);

            $updated = $this->Repository->updatePost($id, $validatedData);
            if (!$updated) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            $post = $this->Repository->getPostById($id);
            return response()->json(new PostResource($post));
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->Repository->deletePost($id);
            if (!$deleted) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function publish(int $id): JsonResponse
    {
        try {
            $published = $this->Repository->publishPost($id);
            if (!$published) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(['message' => 'Post published successfully']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function unpublish(int $id): JsonResponse
    {
        try {
            $unpublished = $this->Repository->unpublishPost($id);
            if (!$unpublished) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(['message' => 'Post unpublished successfully']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getPublished(): AnonymousResourceCollection
    {
        try {
            $posts = $this->Repository->getPublishedPosts($this->req);
            return PostResource::collection($posts);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function incrementViews(int $id): JsonResponse
    {
        try {
            $incremented = $this->Repository->incrementViews($id);
            if (!$incremented) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(['message' => 'Views incremented successfully']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function like(int $id): JsonResponse
    {
        try {
            $liked = $this->Repository->addLike($id, $this->req->user()->id);
            if (!$liked) {
                return response()->json(['message' => 'Post already liked or not found'], 400);
            }
            return response()->json(['message' => 'Post liked successfully']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function unlike(int $id): JsonResponse
    {
        try {
            $unliked = $this->Repository->removeLike($id, $this->req->user()->id);
            if (!$unliked) {
                return response()->json(['message' => 'Post not liked or not found'], 400);
            }
            return response()->json(['message' => 'Post unliked successfully']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}
