<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Repositories\Posts\PostInterface;
use App\Strategies\ContentStrategy;
use App\Strategies\HtmlStrategy;
use App\Strategies\MarkdownStrategy;
use Exception;
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
            $posts = $this->Repository->getAllPosts($this->req, 10);
            return response()->json(['success' => true, 'message' => 'Successfully retrieved posts', 'data' => PostResource::collection($posts)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get posts', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $validatedData = $this->req->validate([
                'title' => 'required|max:255',
                'description' => 'required',
                'category_id' => 'required|exists:categories,id',
                'thumbnail' => 'required|url',
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
            return response()->json(['success' => false, 'message' => 'Failed to create post', 'error' => $e->getMessage()], 500);
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            $post = $this->Repository->getPostById($id);
            if (!$post) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }
            $strategy = $this->getContentStrategy($post->content_type);
            $post->rendered_content = $strategy->renderContent($post->description);
            return response()->json(['success' => true, 'message' => 'Successfully retrieved post', 'data' => new PostResource($post)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve post', 'error' => $e->getMessage()], 500);
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

            $updated = $this->Repository->updatePost($id, $validatedData);
            if ($updated) {
                $updatedPost = $this->Repository->getPostById($id);
                return response()->json(['success' => true, 'message' => 'Successfully updated post', 'data' => new PostResource($updatedPost)], 200);
            }
            return response()->json(['success' => false, 'message' => 'Failed to update post'], 400);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update post', 'error' => $e->getMessage()], 500);
        }
    }

    public function publish(int $id): JsonResponse
    {
        try {
            $published = $this->Repository->publishPost($id);
            if (!$published) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Post published successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to publish post', 'err' => $e->getMessage()], 500);
        }
    }

    public function unpublish(int $id): JsonResponse
    {
        try {
            $unpublished = $this->Repository->unpublishPost($id);
            if (!$unpublished) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Post unpublished successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to unpublish post', 'err' => $e->getMessage()], 500);
        }
    }

    public function getPublished(): JsonResponse
    {
        try {
            $posts = $this->Repository->getPublishedPosts($this->req);
            return response()->json(['success' => true, 'message' => 'Successfully retrieving published posts', 'data' => PostResource::collection($posts)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error failed to get published posts', 'err' => $e->getMessage()], 500);
        }
    }

    public function incrementViews(int $id): JsonResponse
    {
        try {
            $incremented = $this->Repository->incrementViews($id);
            if (!$incremented) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Views incremented successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to increment views', 'err' => $e->getMessage()], 500);
        }
    }

    public function like(int $id): JsonResponse
    {
        try {
            $liked = $this->Repository->addLike($id, $this->req->user()->id);
            if (!$liked) {
                return response()->json(['message' => 'Post already liked or not found'], 400);
            }
            return response()->json(['success' => true, 'message' => 'Post liked successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to like post', 'err' => $e->getMessage()], 500);
        }
    }

    public function unlike(int $id): JsonResponse
    {
        try {
            $unliked = $this->Repository->removeLike($id, $this->req->user()->id);
            if (!$unliked) {
                return response()->json(['message' => 'Post not liked or not found'], 400);
            }
            return response()->json(['success' => true, 'message' => 'Post unliked successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to unliked post', 'err' => $e->getMessage()], 500);
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete post', 'err' => $e->getMessage()], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $restored = $this->Repository->restorePost($id);
            if (!$restored) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Post restored successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to restored post', 'err' => $e->getMessage()], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $deleted = $this->Repository->forceDeletePost($id);
            if (!$deleted) {
                return response()->json(['message' => 'Post not found'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Successfully to permenantly delete post'], 204);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to permenantly delete post', 'err' => $e->getMessage()], 500);
        }
    }

    public function trashed(Request $request): JsonResponse
    {
        try {
            $trashedPosts = $this->Repository->getTrashedPosts($request->input('per_page', 15));
            return response()->json(['success' => true, 'message' => 'Successfully retrieving trashed posts', 'data' => PostResource::collection($trashedPosts)], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error failed to get trashed posts', 'err' => $e->getMessage()], 500);
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
