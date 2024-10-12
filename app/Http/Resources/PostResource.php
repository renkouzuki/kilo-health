<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PostResource extends JsonResource
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
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'read_time' => $this->read_time,
            'views' => $this->views,
            'likes' => $this->likes,
            'published_at' => $this->published_at ? $this->published_at->toISOString() : null,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'icon' => $this->category->icon,
                ];
            }),
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                    'avatar' => $this->author->avatar,
                ];
            }),
            'is_published' => !is_null($this->published_at) && $this->published_at->isPast(),
            'slug' => $this->slug ?? Str::slug($this->title),
            'excerpt' => Str::limit(strip_tags($this->description), 150),
            'read_time_text' => $this->read_time == 1 ? '1 minute read' : "{$this->read_time} minutes read",
        ];
    }
}
