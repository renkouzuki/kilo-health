<?php

namespace App\Services;

interface ContentHandlerInterface
{
    public function format($content): string;
    public function parse($content): string;
}