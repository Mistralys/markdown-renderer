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

## Custom syntax

The library is based on a simple syntax for defining custom commands,
which follow the following scheme with pseudo HTML attributes:

```
{commandName: "value" name="named value" property}
```

The parsing of these commands is handled automatically by the library,
and the attributes can easily be accessed via a helper class when rendering
the matching content.

### Adding a custom processor

If you want to do your own syntax parsing, create a class based on
`BaseProcessor`. Use the `BaseCommandBasedProcessor` class if you want
to use the library's command syntax (easiest to implement). Simply
implement all abstract methods, and you're good to go.

> Every abstract method has documentation on what you are supposed to do.

You can create your processor class anywhere in your project, and add
it to the renderer with the `addProcessor()` method.

```php
use Mistralys\Markdown\MarkdownRenderer;
use My\Custom\CommandProcessor;

$file = FileInfo::factory('/path/to/markdown.md');

echo Renderer::factory($file)
    ->addProcessor(new CommandProcessor($renderer))
    ->render();
```

## Origin

I had created similar Markdown filters in several of my personal and
professional projects. Each project had different enough requirements
for syntax and content, and were easy enough to implement, so for a
long time I did not take the step to move the common functionality to
a separate library.

Another recent project made me take the step. As is often the case, I 
was annoyed enough by having to create regexes to parse the commands
that I decided to do it right.

## Philosophy

I have read quite a few discussions on what kind of information should
be included in a Markdown file. Image widths are one thing that the 
community is divided on. I agree that the width of an image is layout
information, not content. The requirement is real however, and appears
often enough for me to take a pragmatic approach.

My philosophy is that as long as the Markdown content stays easily 
readable, and that the added syntax does not interfere with reading
the document, I don't mind.
