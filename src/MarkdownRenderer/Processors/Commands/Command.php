<?php
/**
 * @package Markdown Renderer
 * @subpackage Commands
 */

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors\Commands;

use AppUtils\ConvertHelper;

/**
 * Represents a single command in the Markdown document.
 * It holds all pertinent information on the command,
 * such as its name, attributes and the callback used to
 * render it.
 *
 * @package Markdown Renderer
 * @subpackage Commands
 */
class Command
{
    private string $name;
    /**
     * @var callable
     */
    private $renderCallback;
    private AttributeList $attributes;

    public function __construct(string $name, callable $renderCallback, string $attributeString)
    {
        $this->name = $name;
        $this->renderCallback = $renderCallback;
        $this->attributes = new AttributeList($this->parseAttributeString($attributeString));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributes() : AttributeList
    {
        return $this->attributes;
    }

    public function render() : string
    {
        return call_user_func($this->renderCallback, $this);
    }

    /**
     * Parses an attribute string into an array containing
     * the different types of attributes found.
     *
     * @param string $string
     * @return BaseAttribute[]
     */
    protected function parseAttributeString(string $string) : array
    {
        // Escape any nested quotes before splitting the string
        $escaped = str_replace('\"', '__QUOT__', trim($string));

        // Split the string into individual characters.
        // We use a simple state machine to detect the
        // different parts of the attribute string.
        $chars = ConvertHelper::string2array($escaped);
        $inQuotes = false;
        $stack = array();
        $name = '';
        $attributes = array();
        for($c=0; $c < count($chars); $c++)
        {
            $char = $chars[$c];

            // Ending quote: Add the current character
            // stack as the value of the attribute.
            // The name (if any) has been stored at the
            // starting quote.
            if($char === '"' && $inQuotes) {
                $attributes[] = array(
                    'name' => $name,
                    'value' => trim(str_replace('__QUOT__', '"', implode('', $stack)))
                );
                $inQuotes = false;
                $stack = array();
                continue;
            }

            // Starting quote: We use all characters collected
            // up to this point as the name of the value.
            // This may be empty, in which case it will be a
            // value without name.
            if($char === '"' && !$inQuotes) {
                $name = trim(implode('', $stack), ' =');
                $inQuotes = true;
                $stack = array();
                continue;
            }

            // Space: If we are not in quotes, the space is
            // used as a boundary between attributes and
            // properties.
            if($char === " " && !$inQuotes) {
                $name = trim(implode('', $stack), ' =');
                if(!empty($name)) {
                    $attributes[] = array(
                        'prop' => $name
                    );
                }
                $stack = array();
                continue;
            }

            // Nothing special: Simply collect this character.
            $stack[] = $chars[$c];
        }

        // Last entry: This can only be a property, or empty space.
        $name = trim(implode('', $stack), ' =');
        if(!empty($name)) {
            $attributes[] = array(
                'prop' => $name
            );
        }

        $result = array();

        // Go through all the collected attributes and add them
        // to the list with the correct type.
        foreach($attributes as $att) {
            if(isset($att['prop'])) {
                $result[] = new PropertyAttribute($att['prop']);
            } else if(!empty($att['name'])) {
                $result[] = new NamedValueAttribute($att['name'], $att['value']);
            } else if(!empty($att['value'])) {
                $result[] = new ValueAttribute($att['value']);
            }
        }

        return $result;
    }
}
