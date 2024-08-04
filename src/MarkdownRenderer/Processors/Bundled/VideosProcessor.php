<?php
/**
 * @package MarkdownRenderer
 * @subpackage Processors
 */

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors\Bundled;

use AppUtils\AttributeCollection;
use AppUtils\FileHelper;
use AppUtils\HTMLTag;
use Closure;
use Mistralys\MarkdownRenderer\Processors\BaseCommandBasedProcessor;
use Mistralys\MarkdownRenderer\Processors\BaseProcessor;
use Mistralys\MarkdownRenderer\Processors\Commands\AttributeList;
use Mistralys\MarkdownRenderer\Processors\Commands\Command;
use Mistralys\MarkdownRenderer\Processors\Commands\ValueAttribute;

/**
 * Converts video shortcode to embedded video players.
 *
 * ## Syntax
 *
 * ### Video stored locally
 *
 * ```
 * {video: "video.mp4"}
 * ```
 *
 * > NOTE: Use {@see self::setVideoFolderURL()} to set the
 * > base URL for the video files.
 *
 * ### Video from URL
 *
 * ```
 * {video: "https://www.example.com/video.mp4&type=video/mp4"}
 * ```
 *
 * ### Sound
 *
 * By default, videos start muted. To unmute them, add the `unmuted` attribute:
 *
 * ```
 * {video: "video.mp4" unmuted}
 * ```
 *
 * ## File type
 *
 * The file mime type is detected automatically. Should this not work,
 * you can specify the type manually:
 *
 * ```
 * {video: "video.mp4" type="video/mp4"}
 * ```
 *
 * @package MarkdownRenderer
 * @subpackage Processors
 */
class VideosProcessor extends BaseCommandBasedProcessor
{
    const OPTION_VIDEO_FOLDER_URL = 'videoFolderURL';
    const OPTION_MISSING_FILE_MESSAGE = 'missingFileMessage';
    const DEFAULT_MISSING_FILE_MESSAGE = 'Video file not specified.';

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
            self::OPTION_MISSING_FILE_MESSAGE => self::DEFAULT_MISSING_FILE_MESSAGE,
            self::OPTION_VIDEO_FOLDER_URL => '/'
        );
    }

    protected function registerCommands(): void
    {
        $this->registerCommand('video', $this->renderVideo(...));
    }

    private function renderVideo(Command $command) : string
    {
        $attributes = $command->getAttributes();

        $file = $attributes->getFirstValue();
        if($file === null) {
            return '';
        }

        $video = HTMLTag::create('video')
            ->prop('controls');

        if(!$attributes->hasProperty('unmuted')) {
            $video->prop('muted');
        }

        $video
            ->attr('style', 'max-width: 100%')
            ->setContent(
                HTMLTag::create('source')
                    ->attr('src', $this->resolveSourceURL($file))
                    ->attr('type', $this->resolveMimeType($file, $attributes))
                .
                'Your browser does not support the video tag.'
            );

        return (string)$video;
    }

    private function resolveSourceURL(ValueAttribute $file) : string
    {
        $src = $file->getValue();

        // Is this an absolute URL?
        if(!str_starts_with(strtolower($src), 'http')) {
            return rtrim($this->getVideoFolderURL(), '/') . '/' . $src;
        }

        return $src;
    }

    private function resolveMimeType(ValueAttribute $file, AttributeList $attributes) : string
    {
        $type = $attributes->getValueByName('type');
        if(!empty($type)) {
            return $type;
        }

        return FileHelper::detectMimeType($file->getValue());
    }
}
