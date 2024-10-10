<?php

namespace App\Http\Controllers;

use App\Repositories\PostViews\PostViewInterface;
use Illuminate\Http\Request;

class PostViewController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(PostViewInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

}
