<?php

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors;

use AppUtils\Interfaces\OptionableInterface;
use Mistralys\MarkdownRenderer\Renderer;

interface ProcessorInterface extends OptionableInterface
{
    public function getRenderer() : Renderer;
    public function preProcess(string $content) : string;
    public function postProcess(string $content) : string;
}
