<?php

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Tests;

use MarkdownRenderer\TestClasses\RendererTestCase;
use Mistralys\MarkdownRenderer\Processors\Bundled\HTMLProcessor;
use Mistralys\MarkdownRenderer\Renderer;

final class HTMLTests extends RendererTestCase
{
    public function test_someText() : void
    {
        $renderer = Renderer::factory('Some text {html}<div>Some HTML</div>{html} More text {html}yo{html}');
        $renderer->addProcessor(new HTMLProcessor($renderer));

        $html = $renderer->render();

        $this->assertTrue($renderer->isValid());

        $this->assertEquals(
            "<p>Some text</p>\n".
            "<div>Some HTML</div>\n".
            "<p>More text</p>\n".
            "yo\n",
            $html
        );
    }

    public function test_newlineHandling() : void
    {
        $markdown =
            "Some text\n".
            "{html}\n".
            "<div>Some HTML</div>\n".
            "{html}\n".
            "More text\n";

        $renderer = Renderer::factory($markdown);
        $renderer->addProcessor(new HTMLProcessor($renderer));

        $html = $renderer->render();

        $this->assertTrue($renderer->isValid());

        $this->assertEquals(
            "<p>Some text</p>\n".
            "\n".
            "<div>Some HTML</div>\n".
            "\n".
            "<p>More text</p>\n",
            $html
        );
    }

    public function test_commandMismatch() : void
    {
        $renderer = Renderer::factory('Some text {html}<div>Some HTML</div> More text');
        $renderer->addProcessor(new HTMLProcessor($renderer));
        $renderer->render();

        $this->assertFalse($renderer->isValid());
        $this->assertTrue($renderer->getResults()->containsCode(HTMLProcessor::VALIDATION_ERROR_UNBALANCED_HTML_COMMANDS));
    }
}
