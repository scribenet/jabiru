<?php

namespace Scribe\Jabiru\Tests\Jabiru\Console\Command;

use Scribe\Jabiru\Console\Application;
use Scribe\Jabiru\Console\Command\JabiruCommand;
use Symfony\Component\Console\Tester\CommandTester;

class JabiruCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * %command.name%
     */
    public function testCommandWithNoArgument()
    {
        $tester = $this->createCommandTester();
        $tester->execute(array());

        $this->assertRegExp('/No input file/', $tester->getDisplay());
    }

    /**
     * %command.name% foo
     */
    public function testCommandWithInvalidArgument()
    {
        $tester = $this->createCommandTester();
        $tester->execute(array('file' => 'foo'));

        $this->assertRegExp('/The input file "foo" not found/', $tester->getDisplay());
    }

    /**
     * %command.name% test/Jabiru/Resources/core/markdown-testsuite/2-paragraphs-hard-return.md
     */
    public function testCommandWithNoOption()
    {
        $file = __DIR__ . '/../../Resources/core/markdown-testsuite/2-paragraphs-hard-return.md';
        $expected = preg_replace('/\r?\n/', "\n", file_get_contents(__DIR__ . '/../../Resources/core/markdown-testsuite/2-paragraphs-hard-return.out'));

        $tester = $this->createCommandTester();
        $tester->execute(array('file' => $file));

        $this->assertEquals($expected, $tester->getDisplay(true));
    }

    /**
     * %command.name% --ext-gfm test/Jabiru/Resources/core/markdown-testsuite/2-paragraphs-line.md
     */
    public function testCommandWithGfmOption()
    {
        $file = __DIR__ . '/../../Resources/core/markdown-testsuite/2-paragraphs-line.md';
        $expected = preg_replace('/\r?\n/', "\n", file_get_contents(__DIR__ . '/../../Resources/core/markdown-testsuite/2-paragraphs-line.out'));

        $tester = $this->createCommandTester();
        $tester->execute(array('file' => $file, '--ext-gfm' => true));

        $this->assertEquals($expected, $tester->getDisplay(true));
    }

    /**
     * %command.name% --format=xhtml test/Jabiru/Resources/core/markdown-testsuite/2-paragraphs-line.md
     */
    public function testCommandWithFormatOption()
    {
        $file = __DIR__ . '/../../Resources/core/markdown-testsuite/2-paragraphs-line.md';
        $expected = preg_replace('/\r?\n/', "\n", file_get_contents(__DIR__ . '/../../Resources/core/markdown-testsuite/2-paragraphs-line.out'));

        $tester = $this->createCommandTester();
        $tester->execute(array('file' => $file, '--format' => 'xhtml'));

        $this->assertEquals($expected, $tester->getDisplay(true));
    }

    /**
     * %command.name% --compress test/Jabiru/Resources/core/markdown-testsuite/2-paragraphs-line.md
     */
    public function testCommandWithCompressOption()
    {
        $file = __DIR__ . '/../../Resources/core/markdown-testsuite/2-paragraphs-line-returns.md';
        $expected = preg_replace('/\r?\n/', '', file_get_contents(__DIR__ . '/../../Resources/core/markdown-testsuite/2-paragraphs-line-returns.out'));

        $tester = $this->createCommandTester();
        $tester->execute(array('file' => $file, '--compress' => true));

        $this->assertEquals($expected, $tester->getDisplay(true));
    }

    /**
     *
     */
    public function testCommandWithLintOption()
    {
        $file   = __DIR__ . '/../../Resources/options/strict/gfm/table-invalid-body.md';
        $tester = $this->createCommandTester();
        $return = $tester->execute(array('file' => $file, '--lint' => true, '--ext-gfm' => true));
        $this->assertEquals(1, $return);

        $file   = __DIR__ . '/../../Resources/gfm/table-simple.md';
        $tester = $this->createCommandTester();
        $return = $tester->execute(array('file' => $file, '--lint' => true, '--ext-gfm' => true));
        $this->assertEquals(0, $return);
    }

    public function testDiagnose()
    {
        $file = __DIR__ . '/../../Resources/core/markdown-testsuite/2-paragraphs-line.md';
        $tester = $this->createCommandTester();
        $tester->execute(['file' => $file, '--diagnose' => true, '--ext-gfm' => true]);
    }

    /**
     * @return CommandTester
     */
    protected function createCommandTester()
    {
        $application = new Application();
        $application->add(new JabiruCommand());
        $command = $application->find('jabiru');

        return new CommandTester($command);
    }

}