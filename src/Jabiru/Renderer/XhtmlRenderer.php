<?php

namespace Scribe\Jabiru\Renderer;

/**
 * Renders markdown result to XHTML format
 */
class XhtmlRenderer extends HtmlRenderer
{

    /**
     * {@inheritdoc}
     */
    protected function getEmptyTagSuffix()
    {
        return ' />';
    }

}