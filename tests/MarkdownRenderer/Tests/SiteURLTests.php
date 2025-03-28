<?php

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Tests;

use MarkdownRenderer\TestClasses\RendererTestCase;
use Mistralys\MarkdownRenderer\Processors\Bundled\SiteURLProcessor;
use Mistralys\MarkdownRenderer\Renderer;

final class SiteURLTests extends RendererTestCase
{
    // region: _Tests

    public function test_linkDefs() : void
    {
        $renderer = Renderer::factory(self::TEST_LINK_DEFS);
        $renderer->addProcessor(new SiteURLProcessor($renderer));

        $html = $renderer->render();

        $this->assertStringContainsString('href="/?param1=value1"', $html);
        $this->assertStringContainsString('href="/Path/To/Page/"', $html);
        $this->assertStringContainsString('href="/Path/To/Page/?param1=value1&amp;param2=value2"', $html);
        $this->assertStringContainsString('href="/"', $html);
    }

    public function test_inlineLinks() : void
    {
        $renderer = Renderer::factory(self::TEST_INLINE_LINKS);
        $renderer->addProcessor(new SiteURLProcessor($renderer));

        $html = $renderer->render();

        $this->assertStringContainsString('href="/?param1=value1"', $html);
        $this->assertStringContainsString('href="/Path/To/Page/"', $html);
        $this->assertStringContainsString('href="/Path/To/Page/?param1=value1&amp;param2=value2"', $html);
        $this->assertStringContainsString('href="/"', $html);
    }

    public function test_setBaseURL() : void
    {
        $renderer = Renderer::factory(self::TEST_INLINE_LINKS);
        $renderer->addProcessor((new SiteURLProcessor($renderer))
            ->setSiteURL('https://127.0.0.1/webroot')
        );

        $html = $renderer->render();

        $this->assertStringContainsString('href="https://127.0.0.1/webroot/?param1=value1"', $html);
        $this->assertStringContainsString('href="https://127.0.0.1/webroot/Path/To/Page/"', $html);
        $this->assertStringContainsString('href="https://127.0.0.1/webroot/Path/To/Page/?param1=value1&amp;param2=value2"', $html);
        $this->assertStringContainsString('href="https://127.0.0.1/webroot/"', $html);
    }

    public function test_paramsCallback() : void
    {
        $renderer = Renderer::factory('[Link1](siteurl?param1=value1)');
        $renderer->addProcessor(
            (new SiteURLProcessor($renderer))
                ->setSiteURL('https://127.0.0.1/webroot')
                ->setParamsCallback($this->adjustParams(...))
        );

        $html = $renderer->render();

        $this->assertTrue($this->paramsCallbackCalled);

        $this->assertStringContainsString('href="https://127.0.0.1/webroot/?foo=bar&amp;param1=value1"', $html);
    }

    /**
     * @param array<string,string> $params
     * @return array<string,string>
     */
    private function adjustParams(array $params) : array
    {
        $params['foo'] = 'bar';

        $this->paramsCallbackCalled = true;

        return $params;
    }

    // endregion

    // region: Support methods

    private const TEST_LINK_DEFS = <<<MD
[LinkDef1][]
[LinkDef2][]
[LinkDef3][]
[LinkDef4][]

[LinkDef1]: siteurl?param1=value1
[LinkDef2]: siteurl/Path/To/Page
[LinkDef3]: siteurl/Path/To/Page?param1=value1&param2=value2
[LinkDef4]: siteurl
MD;

    private const TEST_INLINE_LINKS = <<<MD
[Link1](siteurl?param1=value1)
[Link2](siteurl/Path/To/Page)
[Link3](siteurl/Path/To/Page?param1=value1&param2=value2)
[Link4](siteurl)
MD;

    private bool $paramsCallbackCalled = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paramsCallbackCalled = false;
    }

    // endregion
}
