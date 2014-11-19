<?php

namespace Scribe\Jabiru\Extension\Textile;

use Scribe\Jabiru\Common\Text;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Markdown;

/**
 * [Experimental] Comments
  */
class CommentExtension implements ExtensionInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Markdown $markdown)
    {
        $markdown->on('block', array($this, 'processComment'), 50);
    }

    /**
     * @param Text $text
     */
    public function processComment(Text $text)
    {
        $text->replace('/^###\.[ \t]*(.+?)\n{2,}/m', "\n\n");
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'comment';
    }
}