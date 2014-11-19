<?php

namespace Scribe\Jabiru\Extension;

use Scribe\Jabiru\Markdown;

/**
 * Jabiru extensions must implement ExtensionInterface to register listeners to Markdown
 */
interface ExtensionInterface
{

    /**
     * Adds listeners to EventEmitter
     *
     * @api
     *
     * @param Markdown $markdown
     *
     * @return void
     */
    public function register(Markdown $markdown);

    /**
     * Returns the name of the extension
     *
     * @api
     *
     * @return string
     */
    public function getName();

}