<?php

namespace App\Http\Controllers;

use App\Repositories\SiteSettings\SiteSettingInterface;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    private Request $req;

    protected $Repository;

    public function __construct(SiteSettingInterface $repository, Request $req)
    {
        $this->req = $req;
        $this->Repository = $repository;
    }

}
