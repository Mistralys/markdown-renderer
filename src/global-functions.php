<?php

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer;

use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper_Exception;

/**
 * Fire-and-forget function to render a bit of markdown,
 * without setting any custom options or processors.
 * Use for vanilla Markdown rendering.
 *
 * @param string|FileInfo $markdown Markdown string or file to load it from.
 * @return string
 * @throws FileHelper_Exception
 */
function renderMarkdown(string|FileInfo $markdown) : string
{
    return Renderer::factory($markdown)->render();
}
