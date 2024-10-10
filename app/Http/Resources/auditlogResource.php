<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class auditlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => auditlog::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'first_page_url' => $this->url(1),
                'from' => $this->firstItem(),
                'last_page' => $this->lastPage(),
                'last_page_url' => $this->url($this->lastPage()),
                'next_page_url' => $this->nextPageUrl(),
                'path' => $this->path(),
                'per_page' => $this->perPage(),
                'prev_page_url' => $this->previousPageUrl(),
                'to' => $this->lastItem(),
                'total' => $this->total(),
                'links' => $this->getLinks(),
            ],
        ];
    }

    private function getLinks()
    {
        return [
            [
                'url' => $this->previousPageUrl(),
                'label' => '&laquo; Previous',
                'active' => $this->onFirstPage(),
            ],
            [
                'url' => null,
                'label' => (string) $this->currentPage(),
                'active' => true,
            ],
            [
                'url' => $this->nextPageUrl(),
                'label' => 'Next &raquo;',
                'active' => !$this->hasMorePages(),
            ],
        ];
    }
}
