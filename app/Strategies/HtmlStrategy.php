<?php

namespace App\Strategies;

class HtmlStrategy implements ContentStrategy
{
    public function formatContent(string $content): string
    {
        return $content;
    }

    public function renderContent(string $content): string
    {
        return $content;
    }
}
