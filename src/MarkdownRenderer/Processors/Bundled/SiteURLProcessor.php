<?php
/**
 * @package Markdown Renderer
 * @subpackage Processors
 */

namespace Mistralys\MarkdownRenderer\Processors\Bundled;

use Mistralys\MarkdownRenderer\Processors\BaseProcessor;
use function AppUtils\parseURL;

/**
 * Processor that makes it practical to create website-internal
 * URLs in the Markdown content. Links can be added as per
 * usual in Markdown syntax, but instead of a URL, the
 * special notation `siteurl?` can be used:
 *
 * ```markdown
 * [Link to the homepage]: siteurl
 * [With parameters]: siteurl?param1=value1&param2=value2
 * [To subfolder]: siteurl/Path/To/Page
 * ```
 *
 * > NOTE: This has the added benefit of not having to rewrite any
 * > documents when the website's domain changes (as long as
 * > parameters and paths stay the same).
 *
 * @package Markdown Renderer
 * @subpackage Processors
 */
class SiteURLProcessor extends BaseProcessor
{
    const OPTION_SITE_URL = 'siteURL';

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_SITE_URL => '/'
        );
    }

    public function preProcess(string $content) : string
    {
        $content = $this->preProcessLinkDefinitions($content);
        $content = $this->preProcessInlineLinks($content);

        return $content;
    }

    /**
     * Convert links that use Markdown's inline link syntax, e.g.
     *
     * ```markdown
     * [LinkName](siteurl/Path/To/Page?param1=value1&param2=value2)
     * ```
     *
     * @param string $content
     * @return string
     */
    private function preProcessInlineLinks(string $content) : string
    {
        preg_match_all('/\(siteurl([^)]*)\)/i', $content, $result, PREG_PATTERN_ORDER);

        // Replace all "[LinkName](siteurl?)" notations with the actual URLs.
        foreach($result[0] as $idx => $matchedText)
        {
            $url = parseURL('https://example.com/' . ltrim($result[1][$idx], '/'));

            $content = str_replace($matchedText, '('.$this->buildURL($url->getPath(), $url->getParams()).')', $content);
        }

        return $content;
    }

    /**
     * Convert links that use Markdown's link definition syntax, e.g.
     *
     * ```markdown
     * [LinkName]: siteurl/Path/To/Page?param1=value1&param2=value2
     * ```
     *
     * @param string $content
     * @return string
     */
    private function preProcessLinkDefinitions(string $content) : string
    {
        // Handle the "[LinkName]: siteurl?" notations.
        preg_match_all('/]: siteurl([^\n]*)/i', $content, $result, PREG_PATTERN_ORDER);

        // Replace all "[LinkName]: siteurl?" notations with the actual URLs.
        foreach($result[0] as $idx => $matchedText)
        {
            $url = parseURL('https://example.com/' . ltrim($result[1][$idx], '/'));

            $content = str_replace($matchedText, ']: '.$this->buildURL($url->getPath(), $url->getParams()), $content);
        }

        return $content;
    }

    public function postProcess(string $content): string
    {
        return $content;
    }

    public function setSiteURL(string $url) : self
    {
        return $this->setOption(self::OPTION_SITE_URL, $url);
    }

    public function getSiteURL() : string
    {
        return $this->getOption(self::OPTION_SITE_URL);
    }

    public function buildURL(string $path, array $params=array()) : string
    {
        $url = ltrim($this->getSiteURL(), '/').'/';
        $path = trim($path, '/');

        if(!empty($path)) {
            $url .= $path.'/';
        }

        if(empty($params)) {
            return $url;
        }

        if(!str_contains($url, '?')) {
            $url .= '?';
        }

        return $url.http_build_query($params);
     }
}
