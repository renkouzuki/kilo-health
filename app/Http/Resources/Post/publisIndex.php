<?php

namespace App\Http\Resources\Post;

use App\Traits\getFullThumbnailUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class publisIndex extends JsonResource
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
            'description' => $this->getShortDescription(),
            'thumbnail' => $this->getThisUrl($this->thumbnail),
            'published_at' => $this->published_at instanceof \Carbon\Carbon ? $this->published_at->toISOString() : null,
            'read_time_text' => $this->read_time == 1 ? '1 minute read' : "{$this->read_time} minutes read",
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'icon' => $this->getThisUrl($this->category->icon),
                ];
            }),
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                    'avatar' => $this->author->avatar ? $this->getThisUrl($this->author->avatar) : "https://pbs.twimg.com/media/Fl14K6KaAAQ_OgI?format=jpg&name=large",
                ];
            }),
        ];
    }

    private function getShortDescription(): string
    {
        return strlen($this->description) > 100
            ? substr($this->description, 0, 97) . '...'
            : $this->description;
    }
}
