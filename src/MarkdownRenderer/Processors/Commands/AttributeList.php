<?php
/**
 * @package Markdown Renderer
 * @subpackage Commands
 */

declare(strict_types=1);

namespace Mistralys\MarkdownRenderer\Processors\Commands;

/**
 * Utility class used to access information on a
 * command's attributes, if any.
 *
 * @package Markdown Renderer
 * @subpackage Commands
 */
class AttributeList
{
    /**
     * @var BaseAttribute[]
     */
    private array $attributes;

    /**
     * @param BaseAttribute[] $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return BaseAttribute[]
     */
    public function getAll() : array
    {
        return $this->attributes;
    }

    /**
     * @return ValueAttribute[]
     */
    public function getValues() : array
    {
        $result = array();

        foreach($this->attributes as $attribute)
        {
            if($attribute instanceof ValueAttribute)
            {
                $result[] = $attribute;
            }
        }

        return $result;
    }

    public function getFirstValue() : ?ValueAttribute
    {
        foreach($this->attributes as $attribute)
        {
            if($attribute instanceof ValueAttribute)
            {
                return $attribute;
            }
        }

        return null;
    }

    public function getByName(string $name) : ?NamedValueAttribute
    {
        foreach($this->attributes as $attribute)
        {
            if($attribute instanceof NamedValueAttribute && $attribute->getName() === $name)
            {
                return $attribute;
            }
        }

        return null;
    }

    public function getValueByName(string $name) : string
    {
        $attribute = $this->getByName($name);

        if($attribute !== null)
        {
            return $attribute->getValue();
        }

        return '';
    }
}
