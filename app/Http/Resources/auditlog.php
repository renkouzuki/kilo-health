<?php

namespace App\Http\Resources;

use App\Traits\ModelNameFormatterTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class auditlog extends JsonResource
{
    use ModelNameFormatterTrait;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $changes = $this->decodeAndFormatChanges($this->changes);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'action' => $this->action,
            'model_type' => $this->formatModelName($this->model_type),
            'model_id' => $this->model_id,
            'changes' => $changes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null
        ];
    }
}
