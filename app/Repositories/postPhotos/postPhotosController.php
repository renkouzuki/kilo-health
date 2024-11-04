<?php

namespace App\Repositories\postPhotos;

use App\Events\Posts\PostPhotosCreate;
use App\Events\Posts\PostPhotosDelete;
use App\Events\Posts\PostPhotosUpdate;
use Illuminate\Http\Request;
use App\Models\more_post_photos;
use App\Services\AuditLogService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class postPhotosController implements postPhotosInterface
{
    protected $logService;

    public function __construct(AuditLogService $logService)
    {
        $this->logService = $logService;
    }

    public function uploadMedia(Request $req, int $postId): array
    {
        try {
            $mediaItems = [];
            if ($req->hasFile('file')) {
                foreach ($req->file('file') as $file) {
                    $url = $file->store('more_post_photos', 's3');

                    $mediaItem = more_post_photos::create([
                        'url' => $url,
                        'post_id' => $postId
                    ]);

                    $this->logService->log(Auth::id(), 'upload_post_photos', more_post_photos::class, $mediaItem->id, json_encode(['url' => $url, 'post_id' => $postId]));

                    event(new PostPhotosCreate($mediaItem));

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

    public function getMediaByPost(int $postId, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return more_post_photos::where('post_id', $postId)->latest()->paginate($perPage);
        } catch (ModelNotFoundException $e) {
            throw new Exception('Post not found');
        } catch (Exception $e) {
            Log::error('Error retrieving media: ' . $e->getMessage());
            throw new Exception('Error retrieving media');
        }
    }

    public function deleteMedia(int $mediaId): bool
    {
        try {
            $media = more_post_photos::findOrFail($mediaId);
            $deleted = $media->delete();
            if ($deleted) {
                $this->logService->log(Auth::id(), 'deleted_post_photos', more_post_photos::class, $mediaId, json_encode([
                    'url' => $media->url,
                    'post_id' => $media->post_id
                ]));
                event(new PostPhotosDelete($mediaId, $media->post_id));
            }
            return $deleted;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Media not found');
        } catch (Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            throw new Exception('Error deleting media');
        }
    }

    public function getMediaById(int $mediaId): more_post_photos
    {
        try {
            return more_post_photos::findOrFail($mediaId);
        } catch (ModelNotFoundException $e) {
            throw new Exception('Media not found');
        } catch (Exception $e) {
            Log::error('Error retrieving media: ' . $e->getMessage());
            throw new Exception('Error retrieving media');
        }
    }

    public function updateMedia(Request $req, int $mediaId): bool
    {

        try {
            $media = more_post_photos::findOrFail($mediaId);

            if ($req->filled('post_id')) {
                $media->post_id = $req->post_id;
            }

            if ($req->hasFile('file')) {
                if ($media->url) {
                    Storage::disk('s3')->delete($media->url);
                }
                $media->url = $req->file('file')->store('more_post_photos', 's3');;
            }

            $media->save();

            $this->logService->log(Auth::id(), 'updated_post_photos', more_post_photos::class, $mediaId, json_encode([
                'url' => $media->url,
                'post_id' => $media->post_id
            ]));
            event(new PostPhotosUpdate($mediaId, $media->post_id));

            return true;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Media not found');
        } catch (Exception $e) {
            Log::error('Error updating media: ' . $e->getMessage());
            throw new Exception('Error updating media');
        }
    }

    public function restoreMedia(int $mediaId): bool
    {
        try {
            $media = more_post_photos::withTrashed()->findOrFail($mediaId);
            $restored = $media->restore();
            if ($restored) {
                $this->logService->log(Auth::id(), 'restored_post_photos', more_post_photos::class, $mediaId, json_encode([
                    'url' => $media->url,
                    'post_id' => $media->post_id
                ]));
            }
            return $restored;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Media not found');
        } catch (Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            throw new Exception('Error deleting media');
        }
    }

    public function forceDeleteMedia(int $mediaId): bool
    {
        try {
            $media = more_post_photos::withTrashed()->findOrFail($mediaId);
            $forceDeleted = $media->forceDelete();
            if ($forceDeleted) {
                $dataToDelete = [
                    'url' => $media->url,
                    'post_id' => $media->post_id,
                ];

                $this->logService->log(Auth::id(), 'force_deleted_post_photos', more_post_photos::class, $mediaId, json_encode([
                    'model' => get_class($media),
                    'data' => $dataToDelete
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

    public function getTrashedMedia(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return more_post_photos::onlyTrashed()->when(
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
}
