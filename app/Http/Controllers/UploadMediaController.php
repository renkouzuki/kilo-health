<?php

namespace App\Http\Controllers;

use App\Repositories\UploadMedias\UploadMediaInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadMediaController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(UploadMediaInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

    public function upload(Request $req , int $postId): JsonResponse
    {
        try {
            $this->req->validate([
                'file.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $media = $this->Repository->uploadMedia($this->req, $postId);
            return response()->json(['success' => true, 'message' => 'Successfully uploaded', 'media' => $media], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getMediaByPost(int $postId): JsonResponse
    {
        try {
            $media = $this->Repository->getMediaByPost($postId);
            return response()->json(['success' => true, 'message' => 'Successfully get media', 'media' => $media], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteMedia(int $mediaId): JsonResponse
    {
        try {
            $deleted = $this->Repository->deleteMedia($mediaId);
            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'Media deleted successfully'], 200);
            }
            return response()->json(['success' => false, 'message' => 'Failed to delete media'], 400);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getMedia(int $mediaId): JsonResponse
    {
        try {
            $media = $this->Repository->getMediaById($mediaId);
            if ($media) {
                return response()->json(['success' => true, 'message' => 'Successfully get media', 'media' => $media], 200);
            }
            return response()->json(['success' => false, 'message' => 'Media not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
