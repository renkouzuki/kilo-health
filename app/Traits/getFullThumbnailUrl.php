<?php

namespace App\Traits;

trait getFullThumbnailUrl
{
    protected function getThisUrl(string $thumbnailPath):string{
        return env('AWS_URL') . '/' . ltrim($thumbnailPath, '/');
    }
}
