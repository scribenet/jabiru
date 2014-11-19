<?php

namespace Scribe\Jabiru\Common;

/**
 * HTML/XHTML/XML tag definition
 */
class Element
{

    /**
     * Block level tag
     */
    const TYPE_BLOCK = 'block';

    /**
     * Inline level tag
     */
    const TYPE_INLINE = 'inline';

    /**
     * @var string
     */
    private $name;

    /**
     * @var Text
     */
    private $innerLiteral;

    /**
     * @var Tag
     */
    private $innerObject;

    /**
     * @var Collection
     */
    private $attributes;

    /**
     * @var string
     */
    private $type = self::TYPE_BLOCK;

    /**
     * @var string
     */
    private $emptyTagSuffix = '>';

    /**
     * Constructor
     *
     * @param string $name The name of the tag
     */
    public function __construct($name)
    {
        $this->name         = $name;
        $this->attributes   = new Collection();
        $this->innerLiteral = new Text();
        $this->innerObject  = null;
    }

    /**
     * Sets the inner text
     *
     * @param Text|string $innerLiteral A string to set
     *
     * @return Tag
     */
    public function setInner($inner)
    {
        if ($inner instanceof Tag) {
            $this->innerObject = $inner;
        } else if (!$inner instanceof Text) {
            $this->innerLiteral = new Text($inner);
        } else {
            $this->innerLiteral = $inner;
        }

        return $this;
    }

    /**
     * Gets the inner text
     *
     * @return Text
     */
    public function getInner()
    {
        if ($this->innerObject instanceof Tag) {
            return new Text($this->innerObject->render());
        }

        return $this->innerLiteral;
    }

    public function hasInner()
    {
        return ((!$this->innerObject instanceof Tag) && ($this->innerLiteral->isEmpty() === true)) ? false : true;
    }

    /**
     * Sets the name of the tag
     *
     * @param string $name The name of the tag
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the name of the tag
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the empty tag suffix
     *
     * @param string $emptyTagSuffix The suffix
     *
     * @return Tag
     */
    public function setEmptyTagSuffix($emptyTagSuffix)
    {
        $this->emptyTagSuffix = $emptyTagSuffix;

        return $this;
    }

    /**
     * Returns the empty tag suffix
     *
     * @return string The suffix
     */
    public function getEmptyTagSuffix()
    {
        return $this->emptyTagSuffix;
    }

    /**
     * Sets the type of the tag (Element::TYPE_BLOCK or Element::TYPE_INLINE)
     *
     * @param string $type The type of the tag
     *
     * @return Tag
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the type of the tag
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isBlock()
    {
        return $this->type === self::TYPE_BLOCK;
    }

    /**
     * @return bool
     */
    public function isInline()
    {
        return $this->type === self::TYPE_INLINE;
    }

    /**
     * Sets an attribute
     *
     * @param string $attribute The name of an attribute
     * @param string $value     [optional] The value of an attribute
     *
     * @return Tag
     */
    public function setAttribute($attribute, $value = null)
    {
        $this->attributes->set($attribute, $value);

        return $this;
    }

    /**
     * Sets attributes
     *
     * @param array $attributes An array of attributes
     *
     * @return Tag
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $attr => $value) {
            $this->setAttribute($attr, $value);
        }

        return $this;
    }

    /**
     * Returns a collection of attributes
     *
     * @return Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Renders the tag
     *
     * @return string The HTML string
     */
    public function render()
    {
        $html = new Text();
        $html
            ->append('<')
            ->append($this->getName())
        ;

        foreach ($this->attributes as $name => $value) {
            $html
                ->append(' ')
                ->append($name)
                ->append('=')
                ->append('"')
                ->append($value)
                ->append('"')
            ;
        }

        if ($this->hasInner() === false) {
            if ($this->getType() == self::TYPE_BLOCK) {

                $html
                    ->append('>')
                    ->append('</')
                    ->append($this->getName())
                    ->append('>')
                ;
            } else {

                $html
                    ->append($this->getEmptyTagSuffix())
                ;
            }

            return $html;
        }

        $html
            ->append('>')
            ->append($this->getInner())
            ->append('</')
            ->append($this->getName())
            ->append('>')
        ;

        return (string) $html;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->render();
    }

}