<?php

namespace App\Http\Resources\Topics;

use App\Traits\getFullThumbnailUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class popularTopic extends JsonResource
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
            'name' => $this->name,
            'category_name' => $this->category_name,
            'category_slug' => $this->category_slug,
            'category_icon' => $this->getThisUrl($this->category_icon),
            'post_count' => $this->posts_count
        ];
    }
}
