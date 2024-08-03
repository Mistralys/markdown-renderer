<?php
/**
 * @package MarkdownRenderer
 * @subpackage Processors
 */

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors\Bundled;

use AppUtils\AttributeCollection;
use AppUtils\HTMLTag;
use Mistralys\MarkdownRenderer\Processors\BaseProcessor;

/**
 * Converts video shortcode to embedded video players.
 *
 * ## Syntax
 *
 * ### Video stored locally
 *
 * ```
 * {video:file=video.mp4&type=video/mp4}
 * ```
 *
 * > NOTE: Use {@see self::setVideoFolderURL()} to set the
 * > base URL for the video files.
 *
 * ### Video from URL
 *
 * ```
 * {video:file=https://www.example.com/video.mp4&type=video/mp4}
 * ```
 *
 * @package MarkdownRenderer
 * @subpackage Processors
 */
class VideosProcessor extends BaseProcessor
{
    const OPTION_VIDEO_FOLDER_URL = 'videoFolderURL';

    public function setVideoFolderURL(string $url) : self
    {
        return $this->setOption(self::OPTION_VIDEO_FOLDER_URL, $url);
    }

    public function getVideoFolderURL() : string
    {
        $url = $this->getOption(self::OPTION_VIDEO_FOLDER_URL);
        if(!empty($url)) {
            return $url;
        }

        return '/';
    }

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_VIDEO_FOLDER_URL => '/'
        );
    }

    /**
     * @var array<int,AttributeCollection>
     */
    private array $videos = array();

    public function preProcess(string $content) : string
    {
        preg_match_all('/{video:([^}]+)}/', $content, $result, PREG_PATTERN_ORDER);

        $replaces = array();

        foreach($result[0] as $idx => $matchedText)
        {
            parse_str($result[1][$idx], $result);

            $placeholder = $this->renderer->getNextPlaceholder();

            $this->videos[$placeholder] = AttributeCollection::create($result);

            $replaces[$matchedText] = $placeholder;
        }

        return str_replace(array_keys($replaces), array_values($replaces), $content);
    }

    public function postProcess(string $content) : string
    {
        foreach($this->videos as $placeholder => $attributes)
        {
            $content = str_replace((string)$placeholder, $this->renderVideo($attributes), $content);
        }

        return $content;
    }

    private function renderVideo(AttributeCollection $attributes) : string
    {
        if(!$attributes->hasAttribute('file')) {
            return '';
        }

        $file = $attributes->getAttribute('file');

        // Is this an absolute URL?
        if(str_starts_with($file, 'http')) {
            $src = $file;
        } else {
            $src = rtrim($this->getVideoFolderURL(), '/') . '/' . $file;
        }

        return (string)HTMLTag::create('video')
            ->prop('controls')
            ->prop('muted')
            ->attr('style', 'max-width: 100%')
            ->setContent(
                HTMLTag::create('source')
                    ->attr('src', $src)
                    ->attr('type', $attributes->getAttribute('type', 'video/mp4')).
                'Your browser does not support the video tag.'
            );
    }
}
