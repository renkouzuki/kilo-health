<?php

namespace App\pagination;

class paginating
{
    public function metadata($datas)
    {
        return [
            'current_page' => $datas->currentPage(),
            'first_page_url' => $datas->url(1),
            'from' => $datas->firstItem(),
            'last_page' => $datas->lastPage(),
            'last_page_url' => $datas->url($datas->lastPage()),
            'next_page_url' => $datas->nextPageUrl(),
            'path' => $datas->path(),
            'per_page' => $datas->perPage(),
            'prev_page_url' => $datas->previousPageUrl(),
            'to' => $datas->lastItem(),
            'total' => $datas->total(),
            'links' => $this->getLinks($datas),
        ];
    }

    public function getLinks($data)
    {
        return [
            [
                'url' => $data->previousPageUrl(),
                'label' => '&laquo; Previous',
                'active' => $data->onFirstPage(),
            ],
            [
                'url' => null,
                'label' => (string) $data->currentPage(),
                'active' => true,
            ],
            [
                'url' => $data->nextPageUrl(),
                'label' => 'Next &raquo;',
                'active' => !$data->hasMorePages(),
            ],
        ];
    }
}
