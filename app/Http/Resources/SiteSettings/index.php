<?php

namespace App\Http\Resources\SiteSettings;

use App\Traits\getFullThumbnailUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class index extends JsonResource
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
            'key'=>$this->key,
            'name'=>$this->name,
            'input_type'=>$this->input_type,
            'value'=>$this->input_type === 'image' ? $this->getThisUrl($this->value) : $this->value,
        ];
    }
}
