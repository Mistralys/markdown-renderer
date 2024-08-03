# Markdown Renderer

CommonMark Markdown renderer for PHP with the capability to define
pre- and post-processors with custom syntax.

## Usage

### From a Markdown string

```php
use Mistralys\Markdown\MarkdownRenderer;

$markdown = <<<MD
# Markdown test

Some text with **formatting**.
MD;

echo Renderer::factory($markdown)->render();
```

### From a Markdown file

```php
use Mistralys\Markdown\MarkdownRenderer;
use \AppUtils\FileHelper\FileInfo;

$file = FileInfo::factory('/path/to/markdown.md');

echo Renderer::factory($file)->render();
```

### Adding a processor

Any number of processors can be added with the `addProcessor()` 
method. They are executed in the order that they are added.

Example: Adding the bundled video processor.

```php
use Mistralys\Markdown\MarkdownRenderer;
use Mistralys\MarkdownRenderer\Processors\Bundled\VideosProcessor;

$file = FileInfo::factory('/path/to/markdown.md');

echo Renderer::factory($file)
    ->addProcessor(new VideosProcessor($renderer))
    ->render();
```