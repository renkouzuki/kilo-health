<?php

namespace App\Http\Resources\uploadMedia;

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
            'id'=>$this->id,
            'url'=>$this->getThisUrl($this->url),
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at
        ];
    }
}
