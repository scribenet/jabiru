<?php

namespace Scribe\Jabiru\Component\Collection;

use Scribe\Jabiru\Component\Collection\Collection;
use Scribe\Jabiru\Component\Element\ElementLiteral;

class HashCollection extends AbstractCollection
{
    /**
     * Register a string to be hashed
     *
     * @param  ElementLiteral $text The string to be hashed
     * @return string The hashed string
     */
    public function register(ElementLiteral $text)
    {
        $hash = $this->generateHash($text);

        $this->set($hash, $text);

        return new ElementLiteral($hash);
    }

    /**
     * Generates a hash
     *
     * @param  ElementLiteral $text The string to be hashed
     * @return string The hashed string
     */
    protected function generateHash(ElementLiteral $text)
    {
        return '{boundary:md5(' . md5($text) . ')}';
    }
}