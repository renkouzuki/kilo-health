<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Repositories\Posts\PostInterface;
use App\Strategies\ContentStrategy;
use App\Strategies\HtmlStrategy;
use App\Strategies\MarkdownStrategy;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PostController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(PostInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }


    public function index(): JsonResponse
    {
        try {
            $posts = $this->Repository->getAllPosts($this->req, $this->req->per_page ?? 10);
            return response()->json(['success' => true, 'message' => 'Successfully retrieved posts', 'data' => PostResource::collection($posts)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'title' => 'required|max:255',
                'description' => 'required',
                'category_id' => 'required|exists:categories,id',
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'content_type' => 'required|in:html,markdown',
            ]);

            $strategy = $this->getContentStrategy($validatedData['content_type']);
            $formattedContent = $strategy->formatContent($validatedData['description']);

            $post = $this->Repository->createPost([
                'title' => $validatedData['title'],
                'description' => $formattedContent,
                'content_type' => $validatedData['content_type'],
                'category_id' => $validatedData['category_id'],
                'author_id' => $this->req->user()->id,
                'thumbnail' => $validatedData['thumbnail']
            ]);
            return response()->json(['success' => true, 'message' => 'Successfully created post', 'data' => new PostResource($post)], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            $post = $this->Repository->getPostById($id);
            $strategy = $this->getContentStrategy($post->content_type);
            $post->rendered_content = $strategy->renderContent($post->description);
            return response()->json(['success' => true, 'message' => 'Successfully retrieved post', 'data' => new PostResource($post)], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'title' => 'sometimes|required|max:255',
                'description' => 'sometimes|required',
                'category_id' => 'sometimes|required|exists:categories,id',
                'thumbnail' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'content_type' => 'sometimes|required|in:markdown,html',
            ]);

            $post = $this->Repository->getPostById($id);
            if (!$post) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }

            if (isset($validatedData['description'])) {
                $strategy = $this->getContentStrategy($validatedData['content_type'] ?? $post->content_type);
                $validatedData['description'] = $strategy->formatContent($validatedData['description']);
            }

            $this->Repository->updatePost($id, $validatedData);

            return response()->json(['success' => false, 'message' => 'Failed to update post'], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function publish(int $id): JsonResponse
    {
        try {
            $this->Repository->publishPost($id);
            return response()->json(['success' => true, 'message' => 'Post published successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function unpublish(int $id): JsonResponse
    {
        try {
            $this->Repository->unpublishPost($id);
            return response()->json(['success' => true, 'message' => 'Post unpublished successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPublished(): JsonResponse
    {
        try {
            $posts = $this->Repository->getPublishedPosts($this->req, $this->req->per_page ?? 10);
            return response()->json(['success' => true, 'message' => 'Successfully retrieving published posts', 'data' => PostResource::collection($posts)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function incrementViews(int $id): JsonResponse
    {
        try {
            $this->Repository->incrementViews($id);
            return response()->json(['success' => true, 'message' => 'Views incremented successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function like(int $id): JsonResponse
    {
        try {
            $this->Repository->addLike($id, $this->req->user()->id);
            return response()->json(['success' => true, 'message' => 'Post liked successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function unlike(int $id): JsonResponse
    {
        try {
            $this->Repository->removeLike($id, $this->req->user()->id);
            return response()->json(['success' => true, 'message' => 'Post unliked successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->Repository->deletePost($id);
            if (!$deleted) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully deleted post'], 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $this->Repository->restorePost($id);
            return response()->json(['success' => true, 'message' => 'Post restored successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->Repository->forceDeletePost($id);
            return response()->json(['success' => true, 'message' => 'Successfully to permenantly delete post'], 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function trashed(): JsonResponse
    {
        try {
            $trashedPosts = $this->Repository->getTrashedPosts($this->req, $this->req->per_page ?? 10);
            return response()->json(['success' => true, 'message' => 'Successfully retrieving trashed posts', 'data' => PostResource::collection($trashedPosts)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getContentStrategy(string $contentType): ContentStrategy
    {
        return match ($contentType) {
            'markdown' => new MarkdownStrategy(),
            'html' => new HtmlStrategy(),
            default => throw new InvalidArgumentException("Unsupported content type: $contentType")
        };
    }
}
