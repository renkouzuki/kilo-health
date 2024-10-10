<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class auditlog extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'user_id'=>$this->user_id,
            'action'=>$this->action,
            'model_type'=>$this->model_type,
            'model_id'=>$this->model_id,
            'changes'=>json_decode($this->changes),
            'created_at'=>$this->created_at->toIso8601String(),
            'updated_at'=>$this->updated_at ? $this->updated_at->toIso8601String() : null
        ];
    }
}
