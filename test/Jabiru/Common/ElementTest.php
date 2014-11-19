<?php

namespace Scribe\Jabiru\Tests\Jabiru\Common;

use Scribe\Jabiru\Common\Element;

class ElementTest extends \PHPUnit_Framework_TestCase
{

    public function testSetText()
    {
        $tag = new Element('p');
        $tag->setInner('content');
        $text = $tag->getInner();

        $this->assertInstanceOf('\Scribe\\Jabiru\\Common\\Text', $text);
    }

    public function testRenderTag()
    {
        $tag = new Element('div');
        $tag->setType(Element::TYPE_BLOCK);
        $tag->setInner('foo');
        $tag->setName('p');

        $this->assertEquals('<p>foo</p>', $tag);

        $tag->setEmptyTagSuffix('/>');
        $this->assertEquals('/>', $tag->getEmptyTagSuffix());
        $this->assertEquals(Element::TYPE_BLOCK, $tag->getType());

        $tag->setInner('');
        $this->assertEquals('<p></p>', $tag);
    }

    public function testAttributes()
    {
        $tag = new Element('p');
        $tag->setAttribute('class', 'attr-class');
        $tag->setAttribute('id', 'attr-id');

        $this->assertEquals(array('class' => 'attr-class', 'id' => 'attr-id'), iterator_to_array($tag->getAttributes()));
    }

}
