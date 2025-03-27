<?php
/**
 * @package Markdown Renderer
 * @subpackage Processors
 */

namespace Mistralys\MarkdownRenderer\Processors\Bundled;

use AppUtils\Interfaces\StringableInterface;
use Closure;
use Mistralys\MarkdownRenderer\Processors\BaseProcessor;
use Mistralys\MarkdownRenderer\Processors\ProcessorException;
use function AppUtils\parseURL;
use function AppUtils\parseVariable;

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
    private ?Closure $paramsCallback = null;

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

    /**
     * Sets a callback that can be used to modify the parameters
     * whenever a site URL is generated.
     *
     * Closure parameters:
     *
     * 1. Current parameters as `array(string => string)`.
     * 2. The {@see SiteURLProcessor} instance.
     *
     * Expected return value:
     *
     * The modified parameters as `array(string => string)`.
     *
     * @var Closure|null $callback
     * @return $this
     */
    public function setParamsCallback(?Closure $callback) : self
    {
        $this->paramsCallback = $callback;
        return $this;
    }

    /**
     * @param string $path
     * @param array<string|int,string|int|float|StringableInterface> $params
     * @return string
     */
    public function buildURL(string $path, array $params=array()) : string
    {
        $url = ltrim($this->getSiteURL(), '/').'/';
        $path = trim($path, '/');

        if(!empty($path)) {
            $url .= $path.'/';
        }

        $params = $this->adjustParams($params);

        if(empty($params)) {
            return $url;
        }

        ksort($params);

        if(!str_contains($url, '?')) {
            $url .= '?';
        }

        return $url.http_build_query($params);
     }

    /**
     * @param array<string|int,string|int|float|StringableInterface> $params
     * @return array<string|int,string>
     */
     private function stringifyParams(array $params) : array
     {
         $result = array();
         foreach($params as $key => $value) {
             $result[(string)$key] = (string)$value;
         }

         return $result;
     }

    /**
     * @param array<string|int,string|int|float|StringableInterface> $params
     * @return array<string,string>
     */
     private function adjustParams(array $params) : array
     {
         if(isset($this->paramsCallback))
         {
            $result = ($this->paramsCallback)($this->stringifyParams($params), $this);

            if(!is_array($result)) {
                throw new ProcessorException(
                    'Invalid SiteURL parameter callback return value.',
                    sprintf(
                        'The callback must return an array, [%s] returned.',
                        parseVariable($result)->enableType()->toString()
                    ),
                    ProcessorException::ERROR_INVALID_CALLBACK_RETURN_VALUE
                );
            }

            $params = $result;
         }

         return $this->stringifyParams($params);
     }
}
