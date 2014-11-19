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
class ParagraphExtension implements ExtensionInterface, RendererAwareInterface
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

        $markdown->on('block', array($this, 'buildParagraph'), 120);
    }

    /**
     * @param ElementLiteral $text
     */
    public function buildParagraph(ElementLiteral $text)
    {
        $parts = $text
            ->replace('/\A\n+/', '')
            ->replace('/\n+\z/', '')
            //->replace('/\n+$/', '')
            ->split('/\n{2,}/', PREG_SPLIT_NO_EMPTY);

        $parts->apply(function (ElementLiteral $part) {
            if (!$this->markdown->getHashCollection()->exists($part)) {
                $this->markdown->emit('inline', array($part));
                $part->replace('/^([ \t]*)/', '');

                $part->setString($this->getRenderer()->renderParagraph((string) $part));
            }

            return $part;
        });

        $parts->apply(function (ElementLiteral $part) {
            if ($this->markdown->getHashCollection()->exists($part)) {
                $part->setString(trim($this->markdown->getHashCollection()->get($part)));
            }

            return $part;
        });

        $text->setString($parts->join("\n\n"));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paragraph';
    }

}