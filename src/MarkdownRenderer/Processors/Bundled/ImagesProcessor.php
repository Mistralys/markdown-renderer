<?php
/**
 * @package MarkdownRenderer
 * @subpackage Processors
 */

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors\Bundled;

use AppUtils\HTMLTag;
use AppUtils\StyleCollection;
use Mistralys\MarkdownRenderer\Processors\BaseCommandBasedProcessor;
use Mistralys\MarkdownRenderer\Processors\Commands\AttributeList;
use Mistralys\MarkdownRenderer\Processors\Commands\Command;

/**
 * Adds support for extended image controls in the Markdown document.
 *
 * It uses the following syntax for images:
 *
 * ```
 * {image: "test.jpg" title="Something 'here'" width="150px" class="className"}
 * ```
 *
 * Nested quotes must be escaped like this:
 *
 * ```
 * title="Something \"here\""
 * ```
 *
 * > NOTE: Attributes are not passed through as-is.
 * > Only attributes known by the processor are used, so any
 * > additional attributes are ignored.
 *
 * @package MarkdownRenderer
 * @subpackage Processors
 */
class ImagesProcessor extends BaseCommandBasedProcessor
{
    public const OPTION_MESSAGE_NO_IMAGE_SPECIFIED = 'messageNoImageSpecified';
    public const OPTION_IMAGE_BASE_URL = 'imageBaseURL';
    public const DEFAULT_MESSAGE_NO_IMAGE_FILE_SPECIFIED = 'No image file specified';

    public const ATTRIBUTE_WIDTH = 'width';
    public const ATTRIBUTE_CLASS = 'class';
    public const ATTRIBUTE_TITLE = 'title';
    public const ATTRIBUTE_ID = 'id';
    public const ATTRIBUTE_ALTERNATE_TEXT = 'alt';
    public const ATTRIBUTE_HEIGHT = 'height';

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_MESSAGE_NO_IMAGE_SPECIFIED => self::DEFAULT_MESSAGE_NO_IMAGE_FILE_SPECIFIED,
            self::OPTION_IMAGE_BASE_URL => '/'
        );
    }

    protected function registerCommands(): void
    {
        $this->registerCommand('image', $this->renderImage(...));
    }

    public function getImageBaseURL() : string
    {
        return rtrim($this->getStringOption(self::OPTION_IMAGE_BASE_URL), '/');
    }

    public function setImageBaseURL(string $url) : self
    {
        return $this->setOption(self::OPTION_IMAGE_BASE_URL, $url);
    }

    private function renderImage(Command $command) : string
    {
        $attributes = $command->getAttributes();

        $file = $attributes->getFirstValue();
        if(empty($file)) {
            return $this->getStringOption(self::OPTION_MESSAGE_NO_IMAGE_SPECIFIED);
        }

        $styles = StyleCollection::create();

        $width = $attributes->getByName(self::ATTRIBUTE_WIDTH);
        if($width !== null) {
            $styles->style('width', $width->getValue());
        }

        $height = $attributes->getByName(self::ATTRIBUTE_HEIGHT);
        if($height !== null) {
            $styles->style('height', $height->getValue());
        }

        $src = $file->getValue();
        if(!str_starts_with(strtolower($src), 'http')) {
            $src = $this->getImageBaseURL().'/'.$src;
        }

        $tag = HTMLTag::create('img')
            ->setSelfClosing()
            ->attr('src', $src)
            ->id($attributes->getValueByName(self::ATTRIBUTE_ID))
            ->addClass($attributes->getValueByName(self::ATTRIBUTE_CLASS))
            ->attr('title', $this->resolveTitle($attributes))
            ->attr('alt', $this->resolveAlternateText($attributes), true);

        $styleString = $styles->render();
        if(!empty($styleString)) {
           $tag->attr('style', $styleString);
        }

        return (string)$tag;
    }

    private function resolveTitle(AttributeList $attributes) : string
    {
        $title = $attributes->getValueByName(self::ATTRIBUTE_TITLE);

        if(!empty($title)) {
            return htmlspecialchars($title);
        }

        return '';
    }

    private function resolveAlternateText(AttributeList $attributes) : string
    {
        $alt = $attributes->getValueByName(self::ATTRIBUTE_ALTERNATE_TEXT);
        if(!empty($alt)) {
            return htmlspecialchars($alt);
        }

        return $this->resolveTitle($attributes);
    }
}
