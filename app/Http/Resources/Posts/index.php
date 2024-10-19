<?php

namespace App\Http\Resources\Posts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class index extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->getShortDescription(),
            'category' => $this->category->name,
            'author' => $this->author->name,
            'thumbnail' => $this->thumbnail,
            'published_at' => $this->published_at,
            'views' => $this->views,
            'likes' => $this->likes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];
    }

    private function getShortDescription(): string
    {
        return strlen($this->description) > 100
            ? substr($this->description, 0, 97) . '...'
            : $this->description;
    }
}
