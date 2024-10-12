<?php

namespace App\Strategies;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;

interface ContentStrategy
{
    public function formatContent(string $content): string;
    public function renderContent(string $content): string;
}

class MarkdownStrategy implements ContentStrategy
{
    private $markdownConverter;

    public function __construct()
    {
        $enviroment = new Environment();
        $enviroment->addExtension(new GithubFlavoredMarkdownExtension());
        $this->markdownConverter = new CommonMarkConverter();
    }

    public function formatContent(string $content): string
    {
        return $content;
    }

    public function renderContent(string $content): string
    {
        return $this->markdownConverter->convertToHtml($content)->getContent();
    }
}


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
