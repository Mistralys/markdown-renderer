<?php

declare(strict_types=1);

namespace MarkdownRenderer\Tests;

use MarkdownRenderer\TestClasses\RendererTestCase;
use Mistralys\MarkdownRenderer\Renderer;

final class RendererTests extends RendererTestCase
{

    public function test_createFromString() : void
    {
        $markdown = <<<MD
# Test
Some **bold** text.
MD;

        $rendered = Renderer::factory($markdown)->render();

        $this->assertStringContainsString('<h1 id="test">Test', $rendered);
        $this->assertStringContainsString('<strong>bold</strong>', $rendered);
    }

    public function test_createFromFile() : void
    {
        $rendered = Renderer::factory($this->getAssetsFolder()->getSubFile(RendererTestCase::TEST_FILE_BASIC))->render();

        $this->assertStringContainsString('This file is used in the unit tests.', $rendered);
    }
}
