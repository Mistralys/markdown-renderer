<?php

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Tests;

use MarkdownRenderer\TestClasses\RendererTestCase;
use Mistralys\MarkdownRenderer\Processors\Bundled\ImagesProcessor;
use Mistralys\MarkdownRenderer\Renderer;

final class ImageProcessorTests extends RendererTestCase
{
    public function test_allAttributes() : void
    {
        $renderer = Renderer::factory('{image: "test.jpg" width="45px" property class="className" title="Argh! \"Subquote\" here" "noname" property}');
        $renderer->addProcessor(new ImagesProcessor($renderer));

        $html = $renderer->render();

        $this->assertStringContainsString('src="/test.jpg"', $html);
        $this->assertStringContainsString('style="width:45px"', $html);
        $this->assertStringContainsString('class="className"', $html);
        $this->assertStringContainsString('title="Argh! &quot;Subquote&quot; here"', $html);
    }

    public function test_missingFile() : void
    {
        $renderer = Renderer::factory('{image:}');
        $renderer->addProcessor(new ImagesProcessor($renderer));

        $html = $renderer->render();

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString(ImagesProcessor::DEFAULT_MESSAGE_NO_IMAGE_FILE_SPECIFIED, $html);
    }

    public function test_commandNameIsCaseInsensitive() : void
    {
        $renderer = Renderer::factory('{IMAGE:"test.png"}');
        $renderer->addProcessor(new ImagesProcessor($renderer));

        $html = $renderer->render();

        $this->assertStringContainsString('src="/test.png"', $html);
    }
}
