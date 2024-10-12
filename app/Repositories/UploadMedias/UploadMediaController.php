<?php

namespace App\Repositories\UploadMedias;

use App\Models\upload_media;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadMediaController implements UploadMediaInterface
{
    public function uploadMedia(UploadedFile $file, int $postId): upload_media
    {
        try {
            $path = $file->store('post_media', 'public');

            return upload_media::create([
                'url' => $path,
                'post_id' => $postId
            ]);
        } catch (Exception $e) {
            Log::error('Error uploading media: ' . $e->getMessage());
            throw new Exception('Error uploading media');
        }
    }

    public function getMediaByPost(int $postId): Collection
    {
        try {
            return upload_media::where('post_id', $postId)->get();
        } catch (Exception $e) {
            Log::error('Error retrieving media: ' . $e->getMessage());
            throw new Exception('Error retrieving media');
        }
    }

    public function deleteMedia(int $mediaId): bool
    {
        try {
            $media = upload_media::findOrFail($mediaId);
            Storage::disk('public')->delete($media->url);
            return $media->delete();
        } catch (Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            throw new Exception('Error deleting media');
        }
    }

    public function getMediaById(int $mediaId): ?upload_media
    {
        try {
            return upload_media::find($mediaId);
        } catch (Exception $e) {
            Log::error('Error retrieving media: ' . $e->getMessage());
            throw new Exception('Error retrieving media');
        }
    }
}
