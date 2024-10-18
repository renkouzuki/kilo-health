<?php

namespace App\Repositories\UploadMedias;

use App\Events\Posts\MediaDeleted;
use App\Events\Posts\MediaUploaded;
use App\Models\upload_media;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadMediaController implements UploadMediaInterface
{
    
    protected $logService;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
    }

    public function uploadMedia(Request $req , int $postId): upload_media
    {
        try {
            $data = ['post_id' => $postId];
            if($req->hasFile('file')){
                foreach($req->file('file') as $file){
                    $data['url'][] = $file;
                }
            }

            $media = upload_media::create($data);

            $this->logService->log(Auth::id(), 'uploaded_media', upload_media::class, $media->id, json_encode([
                'post_id' => $postId,
                'url' => $data['url'],
            ]));
            event(new MediaUploaded($media));
            return $media;
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
            $deleted = $media->delete();

            if ($deleted) {
                $this->logService->log(Auth::id(), 'deleted_media', upload_media::class, $mediaId, json_encode([
                    'url' => $media->url,
                    'post_id' => $media->post_id
                ]));
                event(new MediaDeleted($mediaId, $media->post_id));
            }

            return $deleted;
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
