<?php

namespace Scribe\Jabiru\Console\Command;

use Scribe\Jabiru\Jabiru;
use Scribe\Jabiru\Exception\SyntaxError;
use Scribe\Jabiru\Extension\Gfm\FencedCodeBlockExtension;
use Scribe\Jabiru\Extension\Gfm\InlineStyleExtension;
use Scribe\Jabiru\Extension\Gfm\TableExtension;
use Scribe\Jabiru\Extension\Gfm\TaskListExtension;
use Scribe\Jabiru\Extension\Gfm\UrlAutoLinkExtension;
use Scribe\Jabiru\Extension\Gfm\WhiteSpaceExtension;
use Scribe\Jabiru\Renderer\XhtmlRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command-line interface
 */
class JabiruCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('jabiru')
            ->setDescription('Translates markdown into HTML and displays to STDOUT')
            ->addArgument('file', InputArgument::OPTIONAL, 'The input file')
            ->addOption('gfm', null, InputOption::VALUE_NONE, 'Activate Gfm extensions')
            ->addOption('compress', 'c', InputOption::VALUE_NONE, 'Remove whitespace between HTML tags')
            ->addOption('diagnose', null, InputOption::VALUE_NONE, 'Display events and extensions information')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format (html|xhtml)', 'html')
            ->addOption('lint', 'l', InputOption::VALUE_NONE, 'Syntax check only (lint)')
            ->setHelp($this->getHelpContent())
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $content = $this->handleInput($input);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $this->runHelp($input, $output);

            return 1;
        }

        $jabiru = $this->createJabiru($input);

        if ($input->getOption('diagnose')) {
            return $this->diagnose($output, $jabiru, $content);
        }

        if ($input->getOption('lint')) {
            return $this->lint($output, $jabiru, $content);
        }

        $html = $jabiru->render($content);

        if ($input->getOption('compress')) {
            $html = preg_replace('/>\s+</', '><', $html);
        }

        $output->write($html, false, OutputInterface::OUTPUT_RAW);

        return 0;
    }

    /**
     * Get a markdown content from input
     *
     * __Warning: Reading from STDIN always fails on Windows__
     *
     * @param InputInterface $input
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function handleInput(InputInterface $input)
    {
        if ($file = $input->getArgument('file')) {
            if (!file_exists($file)) {
                throw new \InvalidArgumentException(sprintf('The input file "%s" not found', $file));
            }

            return file_get_contents($file);
        } else {
            $contents = '';

            if ($stdin = fopen('php://stdin', 'r')) {
                // Warning: stream_set_blocking always fails on Windows
                if (stream_set_blocking($stdin, false)) {
                    $contents = stream_get_contents($stdin);
                }

                fclose($stdin);
            }

            // @codeCoverageIgnoreStart
            if ($contents) {
                return $contents;
            }
            // @codeCoverageIgnoreEnd
        }

        throw new \InvalidArgumentException('No input file');
    }

    /**
     * Creates an instance of Jabiru
     *
     * @param InputInterface $input The InputInterface instance
     *
     * @return Jabiru|Scribe\Jabiru\Diagnose\Jabiru
     */
    protected function createJabiru(InputInterface $input)
    {
        if ($input->getOption('diagnose')) {
            $jabiru = new \Scribe\Jabiru\Diagnose\Jabiru();
        } else {
            $jabiru = new Jabiru();
        }

        if ($input->getOption('format') == 'xhtml') {
            $jabiru->setRenderer(new XhtmlRenderer());
        }

        if ($input->getOption('gfm')) {
            $jabiru->addExtensions([
                new FencedCodeBlockExtension(),
                new InlineStyleExtension(),
                new TaskListExtension(),
                new WhiteSpaceExtension(),
                new TableExtension(),
                new UrlAutoLinkExtension()
            ]);
        }

        return $jabiru;
    }

    /**
     * Runs help command
     *
     * @param InputInterface  $input  The InputInterface instance
     * @param OutputInterface $output The OutputInterface instance
     *
     * @return int
     */
    protected function runHelp(InputInterface $input, OutputInterface $output)
    {
        /* @var HelpCommand $help */
        $help = $this->getApplication()->find('help');
        $help->setCommand($this);
        $help->run($input, $output);
    }

    /**
     * Lints the content
     *
     * @param OutputInterface $output  The OutputInterface instance
     * @param Jabiru         $jabiru The Jabiru instance
     * @param string          $content The markdown content
     *
     * @return int
     */
    protected function lint(OutputInterface $output, Jabiru $jabiru, $content)
    {
        try {
            $jabiru->render($content, array('strict' => true));
            $output->writeln('No syntax errors detected.');

            return 0;
        } catch (SyntaxError $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return 1;
        }
    }

    /**
     * Diagnose
     *
     * @param OutputInterface           $output
     * @param Scribe\Jabiru\Diagnose\Jabiru $jabiru
     * @param string                    $content
     *
     * @return int
     */
    protected function diagnose(OutputInterface $output, Jabiru $jabiru, $content)
    {
        /* @var TableHelper $table */
        $table = $this->getHelper('table');
        $table->setHeaders([
            'Event', 'Callback', 'Duration', 'MEM Usage'
        ]);

        $table->addRow(['', 'render()', '-', '-']);

        $events = $jabiru->render($content);

        foreach ($events as $event) {
            $table->addRow([
                $event->getEvent(),
                str_repeat('  ', $event->getDepth()) . $event->getCallback(),
                $event->getDuration(),
                $event->getMemory()
            ]);
        }

        $table->render($output);

        return 0;
    }

    /**
     * --help
     *
     * @return string
     */
    protected function getHelpContent()
    {
        return <<< EOT
Translates markdown into html and displays to STDOUT
  <info>%command.name% /path/to/file.md</info>

Following command saves result to file
  <info>%command.name% /path/to/file.md > /path/to/file.html</info>

Or using pipe (On Windows it does't work)
  <info>echo "Markdown is **awesome**" | %command.name%</info>
EOT;
    }

}