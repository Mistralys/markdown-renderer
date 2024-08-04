<?php
/**
 * package Markdown Renderer
 * @subpackage Processors
 */

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors;

use AppUtils\Interfaces\OptionableInterface;
use Mistralys\MarkdownRenderer\Renderer;

/**
 * Interface for all processors.
 *
 * See the following classes if you want to create your
 * own processor:
 *
 * - {@see BaseProcessor} for custom syntax parsing.
 * - {@see BaseCommandBasedProcessor} for commands using the library's command syntax.
 *
 * @package Markdown Renderer
 * @subpackage Processors
 */
interface ProcessorInterface extends OptionableInterface
{
    public function getRenderer() : Renderer;

    /**
     * Do any required adjustments to the Markdown document before
     * it is rendered to HTML.
     *
     * @param string $content The Markdown content.
     * @return string The adjusted content.
     */
    public function preProcess(string $content) : string;

    /**
     * Do any required adjustments to the HTML document after
     * it has been rendered from the Markdown.
     *
     * @param string $content The HTML content.
     * @return string The adjusted HTML content.
     */
    public function postProcess(string $content) : string;
}
