<?php

namespace App\Strategies;

interface ContentStrategy
{
    public function formatContent(string $content): string;
    public function renderContent(string $content): string;
}
