<?php

namespace Scribe\Jabiru\Diagnose;

use Scribe\Jabiru\Jabiru as BaseJabiru;
use Scribe\Jabiru\Component\Element\ElementLiteral;

class Jabiru extends BaseJabiru
{

    /**
     * @param string $text
     * @param array  $options
     *
     * @return Event[]|string
     */
    public function render($text, array $options = array())
    {
        $text = new ElementLiteral($text);
        $markdown = new Markdown($this->getRenderer(), $text, $options);

        $this->registerExtensions($markdown);

        $markdown->start();
        $markdown->emit('initialize', array($text));
        $markdown->emit('block', array($text));
        $markdown->emit('finalize', array($text));

        return $markdown->stop();
    }

}