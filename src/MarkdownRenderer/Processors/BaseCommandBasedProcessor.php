<?php
/**
 * @package Markdown Renderer
 * @subpackage Processors
 */

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors;

use Mistralys\MarkdownRenderer\Processors\Commands\Command;
use Mistralys\MarkdownRenderer\Renderer;

/**
 * Abstract base processor that works using the custom
 * command syntax. It automates command detection and
 * parsing, and provides a simple way to render commands.
 *
 * @package Markdown Renderer
 * @subpackage Processors
 */
abstract class BaseCommandBasedProcessor extends BaseProcessor
{
    /**
     * @var array<string,callable>
     */
    private array $commandCallbacks = array();

    public function __construct(Renderer $renderer)
    {
        parent::__construct($renderer);

        $this->registerCommands();
    }

    /**
     * Use {@see self::registerCommand()} to register all command
     * names that should be detected in the document.
     *
     * @return void
     */
    abstract protected function registerCommands() : void;

    /**
     * Registers a command by name, and the callback used to render it.
     * This will cause all commands with this name to be detected and
     * rendered using the callback.
     *
     * @param string $name The name of the command (The "name" part in `{name: "value"}`).
     * @param callable $renderCallback This will be called to render the command.
     *     It is given a single parameter: the {@see Command} object.
     * @return void
     */
    protected function registerCommand(string $name, callable $renderCallback) : void
    {
        $this->commandCallbacks[$name] = $renderCallback;
    }

    /**
     * @var array<string,Command>
     */
    private array $commandPlaceholders = array();

    public function preProcess(string $content): string
    {
        foreach(array_keys($this->commandCallbacks) as $name) {
            $content = $this->safeguardCommand($name, $content);
        }

        return $content;
    }

    /**
     * Detect all commands with the given name in the content,
     * and replace them with placeholders to safeguard them from
     * any destructive parsing from the Markdown transformation.
     *
     * @param string $name
     * @param string $content
     * @return string
     */
    private function safeguardCommand(string $name, string $content) : string
    {
        preg_match_all('/\{\s*'.$name.'\s*:([^}]*)}/i', $content, $result, PREG_PATTERN_ORDER);

        $replaces = array();

        foreach($result[0] as $idx => $matchedText)
        {
            $placeholder = $this->renderer->getNextPlaceholder();

            // Store the command for later rendering with all
            // necessary information.
            $this->commandPlaceholders[$placeholder] = new Command(
                $name,
                $this->commandCallbacks[$name],
                $result[1][$idx]
            );

            $replaces[$matchedText] = $placeholder;
        }

        return str_replace(array_keys($replaces), array_values($replaces), $content);
    }

    /**
     * At this point, the Markdown transformation has been completed,
     * so we can now render the commands back into the content.
     * This calls the render callback for each of the stored commands
     * and replaces the placeholders with the rendered content.
     *
     * @param string $content
     * @return string
     */
    public function postProcess(string $content): string
    {
        $replaces = array();

        foreach($this->commandPlaceholders as $placeholder => $command) {
            $replaces[$placeholder] = $command->render();
        }

        return str_replace(array_keys($replaces), array_values($replaces), $content);
    }
}
