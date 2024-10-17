<?php

namespace App\Http\Resources;

use App\pagination\paginating;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class anotherPost extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $media = $this->media; 
        $pagination = new paginating();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content_type' => $this->content_type,
            'thumbnail' => $this->thumbnail,
            'read_time' => $this->read_time,
            'published_at' => $this->published_at,
            'views' => $this->views,
            'likes' => $this->likes,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'email' => $this->author->email,
            ],
            'media' => [
                'data' => $media->items(),
                'metadata' => $pagination->metadata($media)
            ],
        ];
    }
}
