<?php

namespace App\Http\Controllers;

use App\Repositories\Topics\TopicInterface;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(TopicInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

}
