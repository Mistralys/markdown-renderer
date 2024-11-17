<?php
/**
 * @package Markdown Renderer
 * @subpackage Renderer
 */

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer;

use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper_Exception;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\OperationResult_Collection;
use AppUtils\Traits\OptionableTrait;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use Mistralys\MarkdownRenderer\Processors\ProcessorInterface;
use function AppLocalize\t;

/**
 * @package Markdown Renderer
 * @subpackage Renderer
 */
class Renderer implements OptionableInterface
{
    use OptionableTrait;

    private string $content;
    private Environment $environment;

    public function __construct(string $content)
    {
        $this->content = $content;
        $this->environment = $this->createEnvironment();
    }

    /**
     * Gets the commonmark environment instance, which
     * allows customizing options before rendering.
     *
     * > NOTE: Call this before {@see self::render()}.
     *
     * @return Environment
     */
    public function getEnvironment() : Environment
    {
        return $this->environment;
    }

    private function createEnvironment() : Environment
    {
        $config = array(
            'heading_permalink' => array(
                'html_class' => 'permalink',
                'id_prefix' => '',
                'apply_id_to_heading' => true,
                'heading_class' => '',
                'fragment_prefix' => '',
                'insert' => 'after',
                'min_heading_level' => 1,
                'max_heading_level' => 6,
                'title' => t('Permalink'),
                'symbol' => '§',
                'aria_hidden' => true,
            )
        );

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        return $environment;
    }

    /**
     * @param string|FileInfo $markdown Either a source file or the markdown content itself.
     * @return Renderer
     * @throws FileHelper_Exception
     */
    public static function factory(string|FileInfo $markdown) : Renderer
    {
        if($markdown instanceof FileInfo) {
            $markdown = $markdown->getContents();
        }

        return new Renderer($markdown);
    }

    public function getDefaultOptions(): array
    {
        return array();
    }

    public function isValid() : bool
    {
        return $this->getResults()->isValid();
    }

    public function getResults() : OperationResult_Collection
    {
        $results = new OperationResult_Collection($this);
        foreach($this->processors as $processor) {
            $results->addResult($processor->getResults());
        }

        return $results;
    }

    public function render() : string
    {
        $this->preProcess();
        $this->process();
        $this->postProcess();

        return $this->content;
    }

    private function preProcess() : void
    {
        foreach($this->processors as $processor) {
            $this->content = $processor->preProcess($this->content);
        }
    }

    private function postProcess() : void
    {
        foreach($this->processors as $processor) {
            $this->content = $processor->postProcess($this->content);
        }
    }

    private function process() : void
    {
        $this->content = (string)(new MarkdownConverter($this->getEnvironment()))
            ->convert($this->content);
    }

    private static int $placeholderCounter = 0;

    public function getNextPlaceholder() : string
    {
        self::$placeholderCounter++;

        return sprintf('P9999999%03d99P', self::$placeholderCounter);
    }

    /**
     * @var ProcessorInterface[]
     */
    private array $processors = array();

    public function addProcessor(ProcessorInterface $processor) : self
    {
        $this->processors[] = $processor;
        return $this;
    }
}
