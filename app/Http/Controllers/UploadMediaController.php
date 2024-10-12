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

    public function upload(int $postId): JsonResponse
    {
        try {
            $this->req->validate([
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $media = $this->Repository->uploadMedia($this->req->file('file'), $postId);
            return response()->json(['media' => $media], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getMediaByPost(int $postId): JsonResponse
    {
        try {
            $media = $this->Repository->getMediaByPost($postId);
            return response()->json(['media' => $media]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteMedia(int $mediaId): JsonResponse
    {
        try {
            $deleted = $this->Repository->deleteMedia($mediaId);
            if ($deleted) {
                return response()->json(['message' => 'Media deleted successfully']);
            }
            return response()->json(['message' => 'Failed to delete media'], 400);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getMedia(int $mediaId): JsonResponse
    {
        try {
            $media = $this->Repository->getMediaById($mediaId);
            if ($media) {
                return response()->json(['media' => $media]);
            }
            return response()->json(['message' => 'Media not found'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
