<?php

namespace Scribe\Jabiru\Extension\Core;

use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Exception\SyntaxError;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Renderer\RendererAwareInterface;
use Scribe\Jabiru\Renderer\RendererAwareTrait;
use Scribe\Jabiru\Markdown;

/**
 * Turn Markdown image shortcuts into <img> tags.
 *
 * Original source code from Markdown.pl
 *
 * > Copyright (c) 2004 John Gruber
 * > <http://daringfireball.net/projects/markdown/>
 */
class ImageExtension implements ExtensionInterface, RendererAwareInterface
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

        $markdown->on('inline', array($this, 'processReferencedImage'), 30);
        $markdown->on('inline', array($this, 'processInlineImage'), 31);
    }

    /**
     * Handle reference-style labeled images: ![alt text][id]
     *
     * @param ElementLiteral  $text
     * @param array $options
     */
    public function processReferencedImage(ElementLiteral $text, array $options = array())
    {
        if (!$text->contains('![')) {
            return;
        }

        /** @noinspection PhpUnusedParameterInspection */
        $text->replace(
            '{
                #(               # wrap whole match in $1
                  !\[
                    (.*?)       # alt text = $2
                  \]

                  [ ]?          # one optional space
                  (?:\n[ ]*)?   # one optional newline followed by spaces

                  \[
                    (.*?)       # id = $3
                  \]
                #)
            }xs',
            function (ElementLiteral $whole, ElementLiteral $alt, ElementLiteral $id = null) use ($options) {
                $result = null;

                if ($id->lower() == '') {
                    $id->setString($alt);
                }

                $this->markdown->emit('escape.special_chars', [$alt->replace('/(?<!\\\\)_/', '\\\\_')]);

                $attr = array(
                    'alt' => $alt->replace('/"/', '&quot;')
                );

                if ($this->markdown->getUrlRegistry()->exists($id)) {
                    $url = new ElementLiteral($this->markdown->getUrlRegistry()->get($id));
                    $url->escapeHtml();

                    if ($this->markdown->getTitleRegistry()->exists($id)) {
                        $title = new ElementLiteral($this->markdown->getTitleRegistry()->get($id));
                        $attr['title'] = $title->escapeHtml();
                    }

                    return $this->getRenderer()->renderImage($url, array('attr' => $attr));
                } else {
                    if ($options['strict']) {
                        throw new SyntaxError(
                            sprintf('Unable to find id "%s" in Reference-style image', $id),
                            $this, $whole, $this->markdown
                        );
                    }

                    return $whole;
                }
            }
        );
    }

    /**
     * handle inline images:  ![alt text](url "optional title")
     *
     * @param ElementLiteral  $text
     * @param array $options
     */
    public function processInlineImage(ElementLiteral $text, array $options = array())
    {
        if (!$text->contains('![')) {
            return;
        }

        /** @noinspection PhpUnusedParameterInspection */
        $text->replace(
            '{
                (               # wrap whole match in $1
                  !\[
                    (.*?)       # alt text = $2
                  \]
                  \(            # literal paren
                    [ \t]*
                    <?(\S+?)>?  # src url = $3
                    [ \t]*
                    (           # $4
                      ([\'"])   # quote char = $5
                      (.*?)     # title = $6
                      \5        # matching quote
                      [ \t]*
                    )?          # title is optional
                  \)
                )
            }xs',
            function (
                ElementLiteral $w,
                ElementLiteral $whole,
                ElementLiteral $alt,
                ElementLiteral $url,
                ElementLiteral $a = null,
                ElementLiteral $q = null,
                ElementLiteral $title = null
            ) use ($options) {
                $this->markdown->emit('escape.special_chars', [$alt->replace('/(?<!\\\\)_/', '\\\\_')]);

                $attr = array(
                    'alt' => $alt->replace('/"/', '&quot;')
                );

                $this->markdown->emit('escape.special_chars', [$url->replace('/(?<!\\\\)_/', '\\\\_')]);
                $url->escapeHtml();

                if ($title) {
                    $attr['title'] = $title->replace('/"/', '&quot;')->escapeHtml();
                }

                return $this->getRenderer()->renderImage($url,  array('attr' => $attr));
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'image';
    }

}
