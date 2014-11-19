<?php

namespace Scribe\Jabiru\Tests\Jabiru\Extension;

use Scribe\Jabiru\Jabiru;
use Scribe\Jabiru\Extension\Gfm\FencedCodeBlockExtension;
use Scribe\Jabiru\Extension\Gfm\InlineStyleExtension;
use Scribe\Jabiru\Extension\Gfm\TableExtension;
use Scribe\Jabiru\Extension\Gfm\TaskListExtension;
use Scribe\Jabiru\Extension\Gfm\UrlAutoLinkExtension;
use Scribe\Jabiru\Extension\Gfm\WhiteSpaceExtension;
use Symfony\Component\Finder\Finder;

/**
 * Tests Scribe\Jabiru\Extensions\Gfm\*
 *
 * @group Markdown
 * @group MarkdownGfm
 */
class GfmExtensionsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test all gfm patterns
     *
     * See `test/Resources/gfm`
     *
     * @param string $name     Name of the test case
     * @param string $markdown The Markdown content
     * @param string $expected Expected output
     *
     * @dataProvider gfmProvider
     */
    public function testGfmPatterns($name, $markdown, $expected)
    {
        $jabiru = new Jabiru();
        $jabiru->addExtensions([
            new FencedCodeBlockExtension(),
            new InlineStyleExtension(),
            new WhiteSpaceExtension(),
            new TaskListExtension(),
            new TableExtension(),
            new UrlAutoLinkExtension()
        ]);

        $expected = str_replace("\r\n", "\n", $expected);
        $expected = str_replace("\r", "\n", $expected);
        $html     = $jabiru->render($markdown);

        $this->assertEquals($expected, $html, sprintf('%s failed', $name));
    }

    /**
     * On strict mode
     *
     * @param string $name     Name of the test case
     * @param string $markdown The Markdown content
     * @param string $expected Expected output
     *
     * @dataProvider strictModeProvider
     * @expectedException Scribe\Jabiru\Exception\SyntaxError
     */
    public function testStrictMode($name, $markdown, $expected)
    {
        $jabiru = new Jabiru();
        $jabiru->addExtensions([
            new FencedCodeBlockExtension(),
            new InlineStyleExtension(),
            new WhiteSpaceExtension(),
            new TaskListExtension(),
            new TableExtension(),
            new UrlAutoLinkExtension()
        ]);

        $html = $jabiru->render($markdown, ['strict' => true]);

        $this->assertEquals($expected, $html, sprintf('%s failed', $name));
    }

    /**
     * @param $name
     * @param $markdown
     * @param $expected
     *
     * @dataProvider highlightCodeBlockEnabled
     */
    public function testHighlightCodeBlockEnabled($name, $markdown, $expected)
    {
        $jabiru = new Jabiru();
        $jabiru->addExtensions([
            new FencedCodeBlockExtension()
        ]);

        $html = $jabiru->render($markdown, ['highlight-code-block' => true]);

        $this->assertEquals($expected, $html, sprintf('%s failed', $name));
    }

    /**
     * @param $name
     * @param $markdown
     * @param $expected
     *
     * @dataProvider highlightCodeBlockDisabled
     */
    public function testHighlightCodeBlockDisabled($name, $markdown, $expected)
    {
        $jabiru = new Jabiru();
        $jabiru->addExtensions([
            new FencedCodeBlockExtension()
        ]);

        $html = $jabiru->render($markdown, ['highlight-code-block' => false]);

        $this->assertEquals($expected, $html, sprintf('%s failed', $name));
    }

    /**
     * @return array
     */
    public function gfmProvider()
    {
        $finder = Finder::create()
            ->in(__DIR__.'/../Resources/gfm')
            ->files()
            ->name('*.md');

        return $this->processPatterns($finder);
    }

    /**
     * @return array
     */
    public function strictModeProvider()
    {
        $finder = Finder::create()
            ->in(__DIR__.'/../Resources/options/strict/gfm')
            ->files()
            ->name('*.md');

        return $this->processPatterns($finder);
    }

    /**
     * @return array
     */
    public function highlightCodeBlockEnabled()
    {
        $finder = Finder::create()
            ->in(__DIR__.'/../Resources/options/highlightjs')
            ->files()
            ->name('enabled-*.md');

        return $this->processPatterns($finder);
    }

    /**
     * @return array
     */
    public function highlightCodeBlockDisabled()
    {
        $finder = Finder::create()
            ->in(__DIR__.'/../Resources/options/highlightjs')
            ->files()
            ->name('disabled-*.md');

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