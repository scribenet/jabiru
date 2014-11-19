<?php

namespace Scribe\Jabiru\Tests\Jabiru;

use Scribe\Jabiru\Markdown;
use Scribe\Jabiru\Renderer\HtmlRenderer;

class MarkdownTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultOptions()
    {
        $markdown = new Markdown(new HtmlRenderer());

        $this->assertEquals([
            'tabWidth'       => 4,
            'nestedTagLevel' => 3,
            'strict'         => false,
            'highlight-code-block'  => true,
            'highlight-code-inline' => true
        ], $markdown->getOptions());
    }

}