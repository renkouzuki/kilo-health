<?php

namespace App\Http\Resources;

use App\Traits\getFullThumbnailUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class anotheruser extends JsonResource
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
            'email' => $this->email,
            'avatar' => $this->avatar ? $this->getThisUrl($this->avatar) : "https://pbs.twimg.com/media/Fl14K6KaAAQ_OgI?format=jpg&name=large",
            'role_id' => $this->role->id,
            'role_name' => $this->role->name
        ];
    }
}
