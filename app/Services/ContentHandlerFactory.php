<?php

namespace App\Services;

class ContentHandlerFactory
{
    public static function getHandler(string $type): ContentHandlerInterface
    {
        return match ($type) {
            'markdown' => new MarkdownContentHandler(),
            'html' => new HtmlContentHandler(),
            default => throw new \InvalidArgumentException("Unsupported content type: $type"),
        };
    }
}