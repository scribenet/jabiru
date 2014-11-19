<?php

namespace Scribe\Jabiru\Extension\Gfm;

use Scribe\Jabiru\Component\Element\Element;
use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Markdown;
use Scribe\Jabiru\Renderer\RendererAwareInterface;
use Scribe\Jabiru\Renderer\RendererAwareTrait;

/**
 * Original source code from GitHub Flavored Markdown
 *
 * > Copyright 2013 GitHub Inc.
 * > https://help.github.com/articles/github-flavored-markdown
 */
class InlineStyleExtension implements ExtensionInterface, RendererAwareInterface
{

    use RendererAwareTrait;

    /**
     * @var array
     */
    private $hashes = array();

    /**
     * {@inheritdoc}
     */
    public function register(Markdown $markdown)
    {
        $markdown->on('inline', array($this, 'processMultipleUnderScore'), 10);
        $markdown->on('inline', array($this, 'processStrikeThrough'), 70);
    }

    /**
     * Multiple underscores in words
     *
     * It is not reasonable to italicize just part of a word, especially when you're dealing with code and names often
     * appear with multiple underscores. Therefore, GFM ignores multiple underscores in words.
     *
     * @param ElementLiteral $text
     */
    public function processMultipleUnderScore(ElementLiteral $text)
    {
        $text->replace('{<pre>.*?</pre>}m', function (ElementLiteral $w) {
            $md5 = md5($w);
            $this->hashes[$md5] = $w;

            return "{gfm-extraction-$md5}";
        });

        $text->replace('/^(?! {4}|\t)(\[?\w+_\w+_\w[\w_]*\]?)/', function (ElementLiteral $w, ElementLiteral $word) {
            $underscores = $word->split('//')->filter(function (ElementLiteral $item) {
                return $item == '_';
            });

            if (count($underscores) >= 2) {
                $word->replaceString('_', '\\_');
            }

            return $word;
        });

        /** @noinspection PhpUnusedParameterInspection */
        $text->replace('/\{gfm-extraction-([0-9a-f]{32})\}/m', function (ElementLiteral $w, ElementLiteral $md5) {
            return "\n\n" . $this->hashes[(string)$md5];
        });
    }

    /**
     * Strike-through `~~word~~` to `<del>word</del>`
     *
     * @param ElementLiteral $text
     */
    public function processStrikeThrough(ElementLiteral $text)
    {
        /** @noinspection PhpUnusedParameterInspection */
        $text->replace('{ (~~) (?=\S) (.+?) (?<=\S) \1 }sx', function (ElementLiteral $w, ElementLiteral $a, ElementLiteral $target) {
            return $this->getRenderer()->renderTag('del', $target, Element::TYPE_INLINE);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'gfmInlineStyle';
    }

}