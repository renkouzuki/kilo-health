<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class publisIndex extends JsonResource
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
            'thumbnail' => $this->thumbnail,
            'published_at' => $this->published_at instanceof \Carbon\Carbon ? $this->published_at->toISOString() : null,
            'is_published' => !is_null($this->published_at) && $this->published_at instanceof \Carbon\Carbon && $this->published_at->isPast(),
            'read_time_text' => $this->read_time == 1 ? '1 minute read' : "{$this->read_time} minutes read",
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
            'slug' => $this->slug ?? $this->id,
        ];
    }

    private function getShortDescription(): string
    {
        return strlen($this->description) > 100
            ? substr($this->description, 0, 97) . '...'
            : $this->description;
    }
}