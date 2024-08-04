<?php

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors\Commands;

class PropertyAttribute extends BaseAttribute
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }
}