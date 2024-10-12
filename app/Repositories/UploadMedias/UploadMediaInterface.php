<?php

namespace App\Repositories\UploadMedias;

use App\Models\upload_media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface UploadMediaInterface {
    public function uploadMedia(UploadedFile $file, int $postId): upload_media;
    public function getMediaByPost(int $postId): Collection;
    public function deleteMedia(int $mediaId): bool;
    public function getMediaById(int $mediaId): ? upload_media;
}
