<?php

namespace App\Http\Resources\PostViews;

use App\Traits\getFullThumbnailUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class user extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    use getFullThumbnailUrl;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'user_id' => $this->user_id,
            'viewed_at' => $this->viewed_at,
            'posts' => [
                'id' => $this->post->id,
                'title' => $this->post->title,
                'description' => $this->post->description,
                'thumbnail' => $this->getThisUrl($this->post->thumbnail)
            ]
        ];
    }
}
