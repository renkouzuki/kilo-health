<?php

namespace App\Http\Controllers;

use App\Http\Resources\uploadMedia\index;
use App\Http\Resources\uploadMedia\posts_mediaId;
use App\Http\Resources\uploadMedia\show;
use App\pagination\paginating;
use App\Repositories\UploadMedias\UploadMediaInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UploadMediaController extends Controller
{
    private Request $req;

    protected $Repository;
    protected $pagination;

    public function __construct(UploadMediaInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
        $this->pagination = new paginating();
    }

    public function index()
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $medias = $this->Repository->getMedias($search, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Successfully get media',
                'data' => index::collection($medias),
                'meta' => $this->pagination->metadata($medias)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function upload(): JsonResponse
    {
        try {
            $this->req->validate([
                'file.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $media = $this->Repository->uploadMedia($this->req);
            return response()->json(['success' => true, 'message' => 'Successfully uploaded', 'media' => $media], 201);
        } catch(ValidationException $e){
            return response()->json(['success' => false , 'message' => 'Oops look like a validation errors occurred' , 'errors' => $e->getMessage()] , 422); 
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    //public function getMediaByPost(int $id): JsonResponse
    //{
    //    try {
    //        $media = $this->Repository->getMediaByPost($id);
    //        return response()->json(['success' => true, 'message' => 'Successfully get media', 'media' => $media], 200);
    //    } catch (Exception $e) {
    //        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //    }
    //}

    public function deleteMedia(int $id): JsonResponse
    {
        try {
            $deleted = $this->Repository->deleteMedia($id);
            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'Media deleted successfully'], 200);
            }
            return response()->json(['success' => false, 'message' => 'Failed to delete media'], 400);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getMedia(int $id): JsonResponse
    {
        try {
            $media = $this->Repository->getMediaById($id);
            if ($media) {
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully get media',
                    'data' => new show($media)
                ], 200);
            }
            return response()->json(['success' => false, 'message' => 'Media not found'], 404);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPostByMediaId(int $id): JsonResponse
    {
        try {
            $search = $this->req->search;
            $perPage = $this->req->per_page ?? 5;
            $post = $this->Repository->displayPostByMediaId($id, $search, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved post',
                'data' => posts_mediaId::collection($post),
                'meta' => $this->pagination->metadata($post)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function trashed(): JsonResponse
    {
        $search = $this->req->search;
        $perPage = $this->req->per_page ?? 10;
        try {
            $medias = $this->Repository->getTrashed($search, $perPage);
            return response()->json([
                'success' => true,
                'message' => 'Successfully get trashed media',
                'data' => index::collection($medias),
                'meta' => $this->pagination->metadata($medias)
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function restored(int $id): JsonResponse
    {
        try {
            $this->Repository->restore($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function forceDeleted(int $id): JsonResponse
    {
        try {
            $this->Repository->forceDelete($id);
            return response()->json([
                'success' => true,
                'message' => 'Successfully force deleted media',
            ], 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
