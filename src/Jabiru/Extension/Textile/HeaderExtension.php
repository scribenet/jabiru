<?php

namespace Scribe\Jabiru\Extension\Textile;

use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Markdown;
use Scribe\Jabiru\Renderer\RendererAwareInterface;
use Scribe\Jabiru\Renderer\RendererAwareTrait;

/**
 * [Experimental] Textile Headers
 *
 * This extension replaces Core\HeaderExtension
  */
class HeaderExtension implements ExtensionInterface, RendererAwareInterface
{

    use RendererAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Markdown $markdown)
    {
        $markdown->on('block', array($this, 'processHeader'), 10);
    }

    /**
     * @param ElementLiteral $text
     */
    public function processHeader(ElementLiteral $text)
    {
        $text->replace('{
            ^h([1-6])  #1 Level
            (|=|>)\.  #2 Align marker
            [ \t]*
            (.+)
            [ \t]*\n+
        }mx', function (ElementLiteral $w, ElementLiteral $level, ElementLiteral $mark, ElementLiteral $header) {
            $attributes = [];
            switch ((string) $mark) {
                case '>':
                    $attributes['align'] = 'right';
                    break;
                case '=':
                    $attributes['align'] = 'center';
                    break;
            }

            return $this->getRenderer()->renderHeader(
                $header,
                ['level' => (int)$level->getString(), 'attr' => $attributes]
            ) . "\n\n";
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