<?php

namespace App\Http\Controllers;

use App\Repositories\UploadMedias\UploadMediaInterface;
use Illuminate\Http\Request;

class UploadMediaController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(UploadMediaInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

}
