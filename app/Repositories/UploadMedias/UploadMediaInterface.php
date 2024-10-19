<?php

namespace App\Repositories\UploadMedias;

use App\Models\upload_media;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface UploadMediaInterface {
    public function uploadMedia(Request $req): array;
    //public function getMediaByPost(int $postId): Collection;
    public function deleteMedia(int $mediaId): bool;
    public function getMediaById(int $mediaId): ? upload_media;
    public function getTrashed(string $search = null, int $perPage = 10) :LengthAwarePaginator;
    public function getMedias(string $search = null, int $perPage = 10) :LengthAwarePaginator;
    public function restore(int $mediaId):bool;
    public function forceDelete(int $mediaId):bool;
    public function displayPostByMediaId(int $mediaId, string $search = null , int $perPage = 5): LengthAwarePaginator;
}
