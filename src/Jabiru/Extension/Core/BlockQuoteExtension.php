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
class BlockQuoteExtension implements ExtensionInterface, RendererAwareInterface
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

        $markdown->on('block', array($this, 'processBlockQuote'), 50);
    }

    /**
     * @param ElementLiteral $text
     */
    public function processBlockQuote(ElementLiteral $text)
    {
        $text->replace('{
          (?:
            (?:
              ^[ \t]*>[ \t]? # \'>\' at the start of a line
                .+\n         # rest of the first line
              (?:.+\n)*      # subsequent consecutive lines
              \n*            # blanks
            )+
          )
        }mx', function (ElementLiteral $bq) {
            $bq->replace('/^[ \t]*>[ \t]?/m', '');
            $bq->replace('/^[ \t]+$/m', '');

            $this->markdown->emit('block', array($bq));

            $bq->replace('|\s*<pre>.+?</pre>|s', function (ElementLiteral $pre) {
                return $pre->replace('/^  /m', '');
            });

            return $this->getRenderer()->renderBlockQuote($bq) . "\n\n";
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'blockquote';
    }

}
