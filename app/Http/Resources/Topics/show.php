<?php

namespace App\Http\Resources\Topics;

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
            'name' => $this->name,
            'category_id' => $this->categorie_id,
            'category' => $this->whenLoaded('categorie', function () {
                return [
                    'id' => $this->categorie->id,
                    'name' => $this->categorie->name,
                    'icon' => $this->getThisUrl($this->categorie->icon),
                    'slug' => $this->categorie->slug
                ];
            })
        ];
    }
}
