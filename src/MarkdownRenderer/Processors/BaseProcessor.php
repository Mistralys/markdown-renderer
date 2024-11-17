<?php

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors;

use AppUtils\OperationResult_Collection;
use AppUtils\Traits\OptionableTrait;
use Mistralys\MarkdownRenderer\Renderer;

abstract class BaseProcessor implements ProcessorInterface
{
    use OptionableTrait;

    protected Renderer $renderer;
    private OperationResult_Collection $validationResults;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
        $this->validationResults = new OperationResult_Collection($this);
    }

    public function getRenderer(): Renderer
    {
        return $this->renderer;
    }

    protected function addError(string $message, int $code) : void
    {
        $this->validationResults->makeError($message, $code);
    }

    protected function getNextPlaceholder() : string
    {
        return $this->renderer->getNextPlaceholder();
    }

    public function getResults() : OperationResult_Collection
    {
        return $this->validationResults;
    }
}
