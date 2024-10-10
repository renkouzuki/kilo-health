<?php

namespace App\Repositories\UploadMedias;

use App\Models\upload_media;

class UploadMediaController {
    public function all(): upload_media
    {
        $category = upload_media::all()->latest();

        return $category;
    }

    public function find(int $id): upload_media
    {
        return upload_media::all()->latest();
    }

    public function create(array $data): void {}

    public function update($id, array $data): void {}

    public function delete($id): void {}
}
