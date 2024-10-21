<?php

namespace App\Http\Resources\Posts;

use App\Traits\getFullThumbnailUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class show extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'rendered_content' => $this->rendered_content,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'icon' => $this->getThisUrl($this->category->icon),
                ];
            }),
            'slug' => $this->slug,
            'content_type' => $this->content_type,
            'author_id' => $this->author_id,
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                    'avatar' => $this->author->avatar ? $this->getThisUrl($this->author->avatar) : "https://pbs.twimg.com/media/Fl14K6KaAAQ_OgI?format=jpg&name=large",
                ];
            }),
            'thumbnail' => $this->getThisUrl($this->thumbnail),
            'published_at' => $this->published_at,
        ];
    }
}
