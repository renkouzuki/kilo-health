<?php

namespace App\Http\Controllers;

use App\Repositories\Posts\PostInterface;
use Illuminate\Http\Request;

class PostController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(PostInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

}
