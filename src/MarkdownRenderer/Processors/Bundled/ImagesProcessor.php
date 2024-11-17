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
    const OPTION_MESSAGE_NO_IMAGE_SPECIFIED = 'messageNoImageSpecified';
    const OPTION_IMAGE_BASE_URL = 'imageBaseURL';
    const DEFAULT_MESSAGE_NO_IMAGE_FILE_SPECIFIED = 'No image file specified';

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

        $width = $attributes->getByName('width');
        if($width !== null) {
            $styles->style('width', $width->getValue());
        }

        $src = $file->getValue();
        if(!str_starts_with(strtolower($src), 'http')) {
            $src = $this->getImageBaseURL().'/'.$src;
        }

        $tag = HTMLTag::create('img')
            ->setSelfClosing()
            ->attr('src', $src)
            ->addClass($attributes->getValueByName('class'))
            ->attr('title', htmlentities($attributes->getValueByName('title')))
            ->attr('alt', htmlentities($attributes->getValueByName('title')));

        $styleString = $styles->render();
        if(!empty($styleString)) {
           $tag->attr('style', $styleString);
        }

        return (string)$tag;
    }
}
