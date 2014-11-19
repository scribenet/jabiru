<?php

namespace Scribe\Jabiru\Tests\Jabiru;

use Scribe\Jabiru\Jabiru;
use Scribe\Jabiru\Extension\Core\EscaperExtension;
use Scribe\Jabiru\Extension\Gfm\InlineStyleExtension;
use Scribe\Jabiru\Renderer\XhtmlRenderer;

class JabiruTest extends \PHPUnit_Framework_TestCase
{

    public function testManipulateExtensions()
    {
        $md = new Jabiru();
        $this->assertTrue($md->hasExtension(new EscaperExtension()));
        $this->assertFalse($md->removeExtension(new EscaperExtension())->hasExtension('escaper'));
        $this->assertTrue($md->addExtension(new InlineStyleExtension())->hasExtension('gfmInlineStyle'));
    }

    public function testRenderer()
    {
        $md = new Jabiru(new XhtmlRenderer());
        $this->assertInstanceOf('Scribe\\Jabiru\\Renderer\\XhtmlRenderer', $md->getRenderer());
    }

    public function testRunTwice()
    {
        $jabiru   = new Jabiru();
        $markdown = file_get_contents(__DIR__.'/Resources/core/markdown-testsuite/link-idref.md');

        $this->assertEquals($jabiru->render($markdown), $jabiru->render($markdown));
    }

}