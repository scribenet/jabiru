<?php

namespace Scribe\Jabiru\Tests\Jabiru\Component\Element;

use Scribe\Jabiru\Component\Element\ElementLiteral;

class ElementLiteralTest extends \PHPUnit_Framework_TestCase
{
    public $class = '\\Scribe\\Jabiru\\Component\\Element\\ElementLiteral';

    public function testStringExpression()
    {
        $text = new ElementLiteral('test');
        $this->assertInternalType('string', (string) $text);
        $this->assertEquals('test', (string) $text);
    }

    public function testMatch()
    {
        $text = new ElementLiteral('abcd1234efgh');
        $this->assertTrue($text->match('/\d{4}/'));
        $this->assertFalse($text->match('/^\d+/'));
    }

    public function testReplaceString()
    {
        $text = new ElementLiteral('<tag></tag>');
        $this->assertEquals('<div></div>', $text->replace('/[a-z]+/', 'div'));
    }

    public function testReplaceCallback()
    {
        $text = new ElementLiteral('<tag></tag>');
        $this->assertEquals('<div></div>', $text->replace('/[a-z]+/', function () {
            return 'div';
        }));
    }

    public function testSplit()
    {
        $text = new ElementLiteral("line1\r\nline2\nline3");
        $items = $text->split('/\r?\n/');

        $this->assertCount(3, $items);
        $this->assertContainsOnlyInstancesOf($this->class, $items);
    }

    public function testLength()
    {
        $text = new ElementLiteral('abcd---');
        $this->assertEquals(7, $text->getLength());

        $text = new ElementLiteral('日本語');
        $this->assertEquals(3, $text->getLength());
    }

    public function testSerializable()
    {
        $text = new ElementLiteral('Lorem Ipsum');

        $serialized = serialize($text);
        $text = unserialize($serialized);

        $this->assertInstanceOf($this->class, $text);
        $this->assertEquals('Lorem Ipsum', (string) $text);
    }

    public function testAppendPrependWrap()
    {
        $text = new ElementLiteral('content');

        $expected = '<p>content</p>';
        $p = $text->append('</p>')->prepend('<p>');
        $this->assertEquals($expected, (string) $p);

        $text = new ElementLiteral('content');
        $this->assertEquals($expected, (string) $text->wrap('<p>', '</p>'));
    }

    public function testCase()
    {
        $text = new ElementLiteral('AbCd');

        $this->assertEquals('abcd', (string) $text->lower());
        $this->assertEquals('ABCD', (string) $text->upper());
    }

    public function testTrim()
    {
        $text = new ElementLiteral('  #Test##    ');
        $this->assertEquals('#Test##', $text->trim());
        $this->assertEquals('Test', $text->trim('#'));

        $text = new ElementLiteral('Test##    ');
        $this->assertEquals('Test##', $text->rtrim());
        $this->assertEquals('Test', $text->rtrim('#'));
    }

}