<?php

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors;

use AppUtils\Traits\OptionableTrait;
use Mistralys\MarkdownRenderer\Renderer;

abstract class BaseProcessor implements ProcessorInterface
{
    use OptionableTrait;

    protected Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getRenderer(): Renderer
    {
        return $this->renderer;
    }
}
