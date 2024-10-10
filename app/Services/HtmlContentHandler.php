<?php

namespace App\Services;

class HtmlContentHandler implements ContentHandlerInterface
{
    public function format($content): string
    {
        return strip_tags($content, '<p><br><strong><em><ul><ol><li><a><img>');
    }

    public function parse($content): string
    {
        return $content;
    }
}