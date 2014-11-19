<?php

namespace Scribe\Jabiru\Extension\Core;

use Scribe\Jabiru\Common\Text;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Renderer\RendererAwareInterface;
use Scribe\Jabiru\Renderer\RendererAwareTrait;
use Scribe\Jabiru\Markdown;

/**
 * Converts horizontal rules
 *
 * Original source code from Markdown.pl
 *
 * > Copyright (c) 2004 John Gruber
 * > <http://daringfireball.net/projects/markdown/>
 */
class HorizontalRuleExtension implements ExtensionInterface, RendererAwareInterface
{

    use RendererAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Markdown $markdown)
    {
        $markdown->on('block', array($this, 'processHorizontalRule'), 20);
    }

    /**
     * @param Text  $text
     */
    public function processHorizontalRule(Text $text)
    {
        $marks = array('\*', '-', '_');

        foreach ($marks as $mark) {
            $text->replace(
                '/^[ ]{0,2}([ ]?' . $mark . '[ ]?){3,}[ \t]*$/m',
                $this->getRenderer()->renderHorizontalRule() . "\n\n"
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hr';
    }

}