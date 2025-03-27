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
        $renderer = Renderer::factory('{image: "test.jpg" width="45px" height="33px" property class="className" alt="Alt text" title="Argh! \"Subquote\" here" id="imageID" "noname" property}');
        $renderer->addProcessor(new ImagesProcessor($renderer));

        $html = $renderer->render();

        $this->assertStringContainsString('src="/test.jpg"', $html);
        $this->assertStringContainsString('style="height:33px;width:45px"', $html);
        $this->assertStringContainsString('class="className"', $html);
        $this->assertStringContainsString('title="Argh! &quot;Subquote&quot; here"', $html);
        $this->assertStringContainsString('alt="Alt text"', $html);
        $this->assertStringContainsString('id="imageID"', $html);
    }

    public function test_freeSpacing() : void
    {
        $image = <<<'MARKDOWN'
{image: 
    "test.jpg" 
    width="42px" 
    class="className"
    title="Title" 
    id="imageID"
}
MARKDOWN;

        $renderer = Renderer::factory($image);
        $renderer->addProcessor(new ImagesProcessor($renderer));

        $html = $renderer->render();

        $this->assertEquals(
            '<p><img src="/test.jpg" id="imageID" title="Title" alt="Title" class="className" style="width:42px"/></p>',
            trim($html)
        );
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
