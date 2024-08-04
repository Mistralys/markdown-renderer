<?php

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors\Commands;

class ValueAttribute extends BaseAttribute
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue() : string
    {
        return $this->value;
    }
}
