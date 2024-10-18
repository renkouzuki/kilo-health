<?php

namespace App\Repositories\postPhotos;

use App\Models\more_post_photos;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface postPhotosInterface
{
    public function uploadMedia(Request $req , int $postId):array;
    public function getMediaByPost(int $postId , int $perPage = 10): LengthAwarePaginator;
    public function deleteMedia(int $mediaId): bool;
    public function getMediaById(int $mediaId): more_post_photos;
    public function updateMedia(Request $req , int $mediaId): bool;
    public function restoreMedia(int $mediaId): bool;
    public function getTrashedMedia(string $search = null, int $perPage = 10): LengthAwarePaginator;
    public function forceDeleteMedia(int $mediaId): bool;
}