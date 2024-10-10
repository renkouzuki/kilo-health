<?php

namespace App\Http\Controllers;

use App\Repositories\Category\CategoryInterface;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(CategoryInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

}
