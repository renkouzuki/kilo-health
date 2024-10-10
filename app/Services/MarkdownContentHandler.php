<?php

namespace App\Services;

use Illuminate\Support\Str;

class MarkdownContentHandler implements ContentHandlerInterface
{
    public function format($content): string
    {
        $content = $this->convertHeaders($content);
        $content = $this->convertBoldAndItalic($content);
        $content = $this->convertLinks($content);
        $content = $this->convertLists($content);

        $content = nl2br($content); // <br> this

        return $content;
    }

    public function parse($content): string
    {
        return $content; 
    }

    //// todo improve this this need to have mis match instead
    private function convertHeaders($content): string
    {
        return preg_replace_callback('/^(#{1,6})\s+(.+)$/m', function($matches) {
            $level = strlen($matches[1]);
            return "<h{$level}>" . trim($matches[2]) . "</h{$level}>";
        }, $content);
    }

    private function convertBoldAndItalic($content): string
    {
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        return preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
    }

    private function convertLinks($content): string
    {
        return preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $content);
    }

    private function convertLists($content): string
    {
        $content = preg_replace_callback('/(?:^|\n)([*-]\s.+)(?:\n|$)/m', function($matches) {
            $items = explode("\n", trim($matches[0]));
            $items = array_map(fn($item) => '<li>' . ltrim($item, '* -') . '</li>', $items);
            return '<ul>' . implode('', $items) . '</ul>';
        }, $content);

        $content = preg_replace_callback('/(?:^|\n)(\d+\.\s.+)(?:\n|$)/m', function($matches) {
            $items = explode("\n", trim($matches[0]));
            $items = array_map(fn($item) => '<li>' . preg_replace('/^\d+\.\s/', '', $item) . '</li>', $items);
            return '<ol>' . implode('', $items) . '</ol>';
        }, $content);

        return $content;
    }
}