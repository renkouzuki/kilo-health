<?php

namespace App\Http\Controllers;

use App\Http\Resources\Post\publishShow;
use App\Http\Resources\Post\publisIndex;
use App\Http\Resources\Posts\index;
use App\Http\Resources\Posts\post_photo;
use App\Http\Resources\Posts\show;
use App\Models\post;
use App\pagination\paginating;
use App\Repositories\Posts\PostInterface;
use App\Strategies\ContentStrategy;
use App\Strategies\HtmlStrategy;
use App\Strategies\MarkdownStrategy;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PostController extends Controller
{
    private Request $req;

    protected $Repository;
    protected $pagination;

    public function __construct(PostInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
        $this->pagination = new paginating();
    }


    public function index(): JsonResponse
    {
        try {
            $posts = $this->Repository->getAllPosts($this->req, $this->req->per_page ?? 5);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved posts',
                'data' => index::collection($posts),
                'meta' => $this->pagination->metadata($posts)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPublished(): JsonResponse
    {
        try {
            $posts = $this->Repository->getPublishedPosts($this->req, $this->req->per_page ?? 10);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieving published posts',
                'data' => publisIndex::collection($posts),
                'meta' => $this->pagination->metadata($posts)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(): JsonResponse
    {
        try {
            $this->req->validate([
                'title' => 'required|max:255',
                'description' => 'required|string',
                'content' => 'required',
                'category_id' => 'required|exists:categories,id',
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'content_type' => 'required|in:html,markdown',
            ]);

            $post = $this->Repository->createPost($this->req);
            return response()->json(['success' => true, 'message' => 'Successfully created post', 'data' => $post], 201);
        } catch(ValidationException $e){
            return response()->json(['success' => false , 'message' => 'Oops look like a validation errors occurred' , 'errors' => $e->getMessage()] , 422); 
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function popularPosts()
    {
        $take = $this->req->take ?? 10;
        try {
            $popularPosts = $this->Repository->getPopularPosts($take, 30);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved popular posts',
                'data' => publisIndex::collection($popularPosts)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $post = $this->Repository->getPostById($id);
            $strategy = $this->getContentStrategy($post->content_type);
            $post->rendered_content = $strategy->renderContent($post->content);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved post',
                'data' => new show($post)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPostPhotosById(int $id): JsonResponse
    {
        $perPage = $this->req->per_page ?? 5;
        try {
            $photos = $this->Repository->displayPostPhotosById($id, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved photos',
                'data' => post_photo::collection($photos),
                'meta' => $this->pagination->metadata($photos)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function publicShow(int $id): JsonResponse
    {
        try {
            $result = $this->Repository->getPostByIdForPublic($id , $this->req->user()->id);
            $post = $result['post'] ?? null;
            if(!$post){
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }
            $strategy = $this->getContentStrategy($post->content_type);
            $post->rendered_content = $strategy->renderContent($post->content);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved post',
                'data' => new publishShow($post),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getRelatedPosts(int $postId): JsonResponse
    {
        $take = $this->req->take ?? 10;
        try {
            $relatedPosts = $this->Repository->getRelatedPosts($postId , $take);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved related posts',
                'data' => publisIndex::collection($relatedPosts)
            ], 200);
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
                'description' => 'sometimes|required|string',
                'content' => 'sometimes|required',
                'category_id' => 'sometimes|required|exists:categories,id',
                'thumbnail' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'content_type' => 'sometimes|required|in:markdown,html',
                'upload_media_id' => 'sometimes|required|exists:upload_media,id',
            ]);

            $post = post::findorfail($id);
            if (!$post) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }

            if (isset($validatedData['content'])) {
                $strategy = $this->getContentStrategy($validatedData['content_type'] ?? $post->content_type);
                $validatedData['content'] = $strategy->formatContent($validatedData['content']);
            }

            $this->Repository->updatePost($id, $this->req);

            return response()->json(['success' => true, 'message' => 'successfully to update post'], 200);
        } catch(ValidationException $e){
            return response()->json(['success' => false , 'message' => 'Oops look like a validation errors occurred' , 'errors' => $e->getMessage()] , 422); 
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

    public function toggleLikes(int $id): JsonResponse
    {
        try {
            $this->Repository->toggleLike($id, $this->req->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Post like status updated successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieving trashed posts',
                'data' => index::collection($trashedPosts),
                'meta' => $this->pagination->metadata($trashedPosts)
            ], 200);
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
