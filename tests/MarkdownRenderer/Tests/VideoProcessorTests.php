<?php

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Tests;

use MarkdownRenderer\TestClasses\RendererTestCase;
use Mistralys\MarkdownRenderer\Processors\Bundled\VideosProcessor;
use Mistralys\MarkdownRenderer\Renderer;

class VideoProcessorTests extends RendererTestCase
{
    public function test_localVideoWithoutURL() : void
    {
        $renderer = Renderer::factory('{video:file=video.mp4&type=video/mp4}');
        $renderer->addProcessor(new VideosProcessor($renderer));

        $rendered = $renderer->render();

        $this->assertStringContainsString('/video.mp4', $rendered);
        $this->assertStringContainsString('video/mp4', $rendered);
        $this->assertStringContainsString('<video', $rendered);
    }

    public function test_localVideoWithURL() : void
    {
        $renderer = Renderer::factory('{video:file=video.mp4&type=video/mp4}');

        $renderer->addProcessor((new VideosProcessor($renderer))
            ->setVideoFolderURL('"/videos/video.mp4"')
        );

        $rendered = $renderer->render();

        $this->assertStringContainsString('"/videos/video.mp4"', $rendered);
    }

    public function test_externalVideoURL() : void
    {
        $renderer = Renderer::factory('{video:file=https://external.com/video.mp4}');

        $renderer->addProcessor((new VideosProcessor($renderer))
            ->setVideoFolderURL('/videos/')
        );

        $rendered = $renderer->render();

        $this->assertStringContainsString('"https://external.com/video.mp4"', $rendered);
    }
}
