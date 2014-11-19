<?php

namespace Scribe\Jabiru\Tests\Jabiru\Extension;

use Scribe\Jabiru\Jabiru;
use Scribe\Jabiru\Renderer\XhtmlRenderer;
use Scribe\Jabiru\Extension\Html\AttributesExtension;
use Symfony\Component\Finder\Finder;

/**
 * Tests Scribe\Jabiru\Extensions\Html\*
 *
 * @group Html
 */
class HtmlExtensionsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider htmlProvider
     */
    public function testHtmlPatterns($name, $textile, $expected)
    {
        $jabiru = new Jabiru(new XhtmlRenderer());
        $jabiru->addExtensions([
            new AttributesExtension()
        ]);

        $expected = str_replace("\r\n", "\n", $expected);
        $expected = str_replace("\r", "\n", $expected);
        $html     = $jabiru->render($textile);

        $this->assertEquals($expected, $html, sprintf('%s failed', $name));
    }

    /**
     * @return array
     */
    public function htmlProvider()
    {
        $finder = Finder::create()
            ->in([__DIR__.'/../Resources/html', __DIR__.'/../Resources/core'])
            ->files()
            ->name('*.md')
            ->notName('link-automatic-email.md');

        return $this->processPatterns($finder);
    }

    /**
     * @param Finder|\Symfony\Component\Finder\SplFileInfo[] $finder
     *
     * @return array
     */
    protected function processPatterns(Finder $finder)
    {
        $patterns = [];

        foreach ($finder as $file) {
            $name       = preg_replace('/\.(md|out)$/', '', $file->getFilename());
            $expected   = trim(file_get_contents(preg_replace('/\.md$/', '.out', $file->getPathname())));
            $expected   = str_replace("\r\n", "\n", $expected);
            $expected   = str_replace("\r", "\n", $expected);
            $patterns[] = [$name, $file->getContents(), $expected];
        }

        return $patterns;
    }

}