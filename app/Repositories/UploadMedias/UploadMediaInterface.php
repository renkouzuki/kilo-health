<?php

namespace App\Repositories\UploadMedias;

use App\Models\upload_media;

interface UploadMediaInterface {
    public function all():upload_media;
    public function find(int $id):upload_media;
    public function create(array $data):void;
    public function update(int $id, array $data):void;
    public function delete(int $id):void;
}
