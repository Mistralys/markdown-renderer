<?php
/**
 * @package MarkdownRenderer
 * @subpackage Processors
 */

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors\Bundled;

use Mistralys\MarkdownRenderer\Processors\BaseProcessor;

/**
 * Allows embedding HTML snippets in the Markdown content.
 *
 * ## Usage
 *
 * Each snippet must be enclosed in `{html}` commands. For example:
 *
 * ```markdown
 * Some text
 * {html}
 * <div>Some HTML</div>
 * {html}
 * More text
 * ```
 *
 * @package MarkdownRenderer
 * @subpackage Processors
 */
class HTMLProcessor extends BaseProcessor
{
    public const VALIDATION_ERROR_UNBALANCED_HTML_COMMANDS = 167601;

    /**
     * @var array<string,string>
     */
    private array $htmlSnippets = array();

    public function getDefaultOptions(): array
    {
        return array();
    }

    public function preProcess(string $content): string
    {
        if(!str_contains($content, '{html}')) {
            return $content;
        }

        $parts = explode('{html}', $content);

        // check if there is an even number of parts
        if(count($parts) % 2 === 0) {
            $this->addError(
                'The {html} commands are not balanced, there is one too many or not enough.',
                self::VALIDATION_ERROR_UNBALANCED_HTML_COMMANDS
            );
            return $content;
        }

        $contents = '';
        foreach($parts as $index => $part)
        {
            if($index % 2 === 0) {
                $contents .= $part;
                continue;
            }

            $placeholder = $this->getNextPlaceholder();
            $contents .= PHP_EOL.PHP_EOL.$placeholder.PHP_EOL.PHP_EOL;
            $this->htmlSnippets[$placeholder] = $part;
        }

        return $contents;
    }

    public function postProcess(string $content): string
    {
        foreach($this->htmlSnippets as $placeholder => $html) {
            $content = str_replace('<p>'.$placeholder.'</p>', $html, $content);
        }

        return $content;
    }
}
