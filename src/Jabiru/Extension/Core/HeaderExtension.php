<?php

namespace Scribe\Jabiru\Extension\Core;

use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Renderer\RendererAwareInterface;
use Scribe\Jabiru\Renderer\RendererAwareTrait;
use Scribe\Jabiru\Markdown;

/**
 * Converts text to <blockquote>
 *
 * Original source code from Markdown.pl
 *
 * > Copyright (c) 2004 John Gruber
 * > <http://daringfireball.net/projects/markdown/>
 */
class HeaderExtension implements ExtensionInterface, RendererAwareInterface
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

        $markdown->on('block', array($this, 'processSetExtHeader'), 10);
        $markdown->on('block', array($this, 'processAtxHeader'), 11);
    }

    /**
     * @param ElementLiteral $text
     */
    public function processSetExtHeader(ElementLiteral $text)
    {
        /** @noinspection PhpUnusedParameterInspection */
        $text->replace('{^(.+)[ \t]*\n(=+|-+)[ \t]*\n+}m', function (ElementLiteral $whole, ElementLiteral $content, ElementLiteral $mark) {
            $level = (substr($mark, 0, 1) == '=') ? 1 : 2;

            $this->markdown->emit('inline', array($content));

            return $this->getRenderer()->renderHeader($content, array('level' => $level)) . "\n\n";
        });
    }

    /**
     * @param ElementLiteral $text
     */
    public function processAtxHeader(ElementLiteral $text)
    {
        /** @noinspection PhpUnusedParameterInspection */
        $text->replace('{
            ^(\#{1,6})  # $1 = string of #\'s
            [ \t]*
            (.+?)       # $2 = Header text
            [ \t]*
            \#*         # optional closing #\'s (not counted)
            \n+
        }mx', function (ElementLiteral $whole, ElementLiteral $marks, ElementLiteral $content) {
            $level = strlen($marks);

            $this->markdown->emit('inline', array($content));

            return $this->getRenderer()->renderHeader($content, array('level' => $level)) . "\n\n";
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'header';
    }

}
