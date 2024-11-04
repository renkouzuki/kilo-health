<?php

namespace App\Repositories\UploadMedias;

use App\Events\Posts\MediaDeleted;
use App\Events\Posts\MediaUploaded;
use App\Models\post;
use App\Models\upload_media;
use App\pagination\paginating;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadMediaController implements UploadMediaInterface
{

    protected $logService;
    protected $pagination;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
        $this->pagination = new paginating();
    }

    public function getMedias(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return upload_media::query()->when(
                $search ?? null,
                fn($query, $search) =>
                $query->where(
                    fn($q) =>
                    $q->where('url', 'LIKE', "%{$search}%")
                )
            )->latest()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            throw new Exception('Error deleting media');
        }
    }

    public function uploadMedia(Request $req): array
    {
        try {
            $mediaItems = [];

            if ($req->hasFile('file')) {
                foreach ($req->file('file') as $file) {
                    $url = $file->store('upload_media', 's3');

                    $mediaItem = upload_media::create(['url' => $url]);

                    $this->logService->log(Auth::id(), 'uploaded_media', upload_media::class, $mediaItem->id, json_encode(['url' => $url]));

                    event(new MediaUploaded($mediaItem));

                    $mediaItems[] = $mediaItem;
                }
            }else{
                throw new Exception('No media uploaded');
            }

            return $mediaItems;
        } catch (Exception $e) {
            Log::error('Error uploading media: ' . $e->getMessage());
            throw new Exception('Error uploading media');
        }
    }


    //public function getMediaByPost(int $postId): Collection
    //{
    //    try {
    //        return upload_media::where('post_id', $postId)->get();
    //    } catch (Exception $e) {
    //        Log::error('Error retrieving media: ' . $e->getMessage());
    //        throw new Exception('Error retrieving media');
    //    }
    //}

    public function getMediaById(int $mediaId): ?upload_media
    {
        try {
            return upload_media::findOrFail($mediaId);
        } catch (ModelNotFoundException $e) {
            throw new Exception('Media not found');
        } catch (Exception $e) {
            Log::error('Error retrieving media: ' . $e->getMessage());
            throw new Exception('Error retrieving media');
        }
    }

    public function displayPostByMediaId(int $mediaId, string $search = null, int $perPage = 5): LengthAwarePaginator
    {
        try {
            return post::where('upload_media_id', $mediaId)->select('id', 'title', 'description', 'thumbnail')->when(
                $search ?? null,
                fn($query, $search) =>
                $query->where(
                    fn($q) =>
                    $q->where('title', 'LIKE', "%{$search}%")
                )
            )->latest()->paginate($perPage);
        } catch (ModelNotFoundException) {
            throw new Exception('Media not found');
        } catch (Exception $e) {
            Log::error('Error retrieving media: ' . $e->getMessage());
            throw new Exception('Error retrieving media');
        }
    }


    public function deleteMedia(int $mediaId): bool
    {
        try {
            $media = upload_media::findOrFail($mediaId);
            $deleted = $media->delete();
            if ($deleted) {
                Storage::disk('s3')->delete($media->url);
                $this->logService->log(Auth::id(), 'deleted_media', upload_media::class, $mediaId, json_encode([
                    'url' => $media->url,
                ]));
                event(new MediaDeleted($mediaId));
            }

            return $deleted;
        } catch (Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            throw new Exception('Error deleting media');
        }
    }

    public function getTrashed(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return upload_media::onlyTrashed()->when(
                $search ?? null,
                fn($query, $search) =>
                $query->where(
                    fn($q) =>
                    $q->where('url', 'LIKE', "%{$search}%")
                )
            )->latest()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            throw new Exception('Error deleting media');
        }
    }

    public function restore(int $mediaId): bool
    {
        try {
            $restored = upload_media::withTrashed()->findOrFail($mediaId)->restore();
            if ($restored) {
                $this->logService->log(Auth::id(), 'restored_media', upload_media::class, $mediaId, null);
            }
            return $restored;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Media not found');
        } catch (Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            throw new Exception('Error deleting media');
        }
    }

    public function forceDelete(int $mediaId): bool
    {
        try {
            $uploadmedia = upload_media::withTrashed()->findOrFail($mediaId);

            $dataToDelete = [
                'id' => $uploadmedia->id,
                'url' => $uploadmedia->url,
            ];

            $forceDeleted = $uploadmedia->forceDelete();

            if ($forceDeleted) {
                $this->logService->log(Auth::id(), 'force_deleted_media', upload_media::class, $mediaId, json_encode([
                    'model' => get_class($uploadmedia),
                    'data' => $dataToDelete,
                ]));
            }

            return $forceDeleted;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Media not found');
        } catch (Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            throw new Exception('Error deleting media');
        }
    }
}
