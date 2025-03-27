<?php
/**
 * @package Markdown Renderer
 * @subpackage Processors
 */

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors;

use Mistralys\MarkdownRenderer\MarkdownRendererException;

/**
 * @package Markdown Renderer
 * @subpackage Processors
 */
class ProcessorException extends MarkdownRendererException
{
    const ERROR_INVALID_CALLBACK_RETURN_VALUE = 176201;
}
