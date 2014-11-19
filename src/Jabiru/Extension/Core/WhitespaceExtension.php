<?php

namespace Scribe\Jabiru\Extension\Core;

use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Renderer\RendererAwareInterface;
use Scribe\Jabiru\Renderer\RendererAwareTrait;
use Scribe\Jabiru\Markdown;

/**
 * Original source code from Markdown.pl
 *
 * > Copyright (c) 2004 John Gruber
 * > <http://daringfireball.net/projects/markdown/>
 */
class WhitespaceExtension implements ExtensionInterface, RendererAwareInterface
{

    use RendererAwareTrait;

    /**
     * @var Markdown
     */
    private $markdown;

    /**
     * {@inheritdoc}
     */
    public function register(Markdown $markdown)
    {
        $this->markdown = $markdown;

        $markdown->on('initialize', array($this, 'initialize'), 10);
        $markdown->on('detab', array($this, 'detab'), 10);
        $markdown->on('outdent', array($this, 'outdent'), 10);
        $markdown->on('inline', array($this, 'processHardBreak'), 80);
    }

    /**
     * Convert line breaks
     *
     * @param ElementLiteral $text
     */
    public function initialize(ElementLiteral $text)
    {
        $text->replaceString("\r\n", "\n");
        $text->replaceString("\r", "\n");

        $text->append("\n\n");
        $this->markdown->emit('detab', array($text));
        $text->replace('/^[ \t]+$/m', '');
    }

    /**
     * Convert tabs to spaces
     *
     * @param Text  $text
     * @param array $options
     */
    public function detab(ElementLiteral $text, array $options = array())
    {
        /** @noinspection PhpUnusedParameterInspection */
        $text->replace('/(.*?)\t/', function (ElementLiteral $whole, ElementLiteral $string) use ($options) {
            return $string . str_repeat(' ', $options['tabWidth'] - $string->getLength() % $options['tabWidth']);
        });
    }

    /**
     * Remove one level of line-leading tabs or spaces
     *
     * @param Text  $text
     * @param array $options
     */
    public function outdent(ElementLiteral $text, array $options = array())
    {
        $text->replace('/^(\t|[ ]{1,' . $options['tabWidth'] . '})/m', '');
    }

    /**
     * @param Text  $text
     */
    public function processHardBreak(ElementLiteral $text)
    {
        $text->replace('/ {2,}\n/', $this->getRenderer()->renderLineBreak() .  "\n");
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'whitespace';
    }

}