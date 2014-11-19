<?php

namespace Scribe\Jabiru\Exception;

use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Markdown;

/**
 * Syntax Error ("strict" mode)
 */
class SyntaxError extends MarkdownException
{

    /**
     * @var ExtensionInterface
     */
    private $extension;

    /**
     * @var Text
     */
    private $text;

    /**
     * @var Markdown
     */
    private $markdown;

    /**
     * @var integer
     */
    private $markdownLineNo = 0;

    /**
     * @var string
     */
    private $rawMessage = '';

    /**
     * @param string             $message   [optional] The Exception message to throw.
     * @param ExtensionInterface $extension [optional] The extension that triggers this error
     * @param Text               $text      [optional] The text contains syntax error
     * @param Markdown           $markdown  [optional]
     * @param \Exception         $previous  [optional] The previous exception used for the exception chaining
     */
    public function __construct(
        $message = '',
        ExtensionInterface $extension = null,
        ElementLiteral $text = null,
        Markdown $markdown = null,
        \Exception $previous = null
    ) {
        parent::__construct('', 0, $previous);

        $this->extension = $extension;
        $this->text = $text;
        $this->markdown = $markdown;
        $this->rawMessage = $message;

        $this->guessLineOfCode();
        $this->generateMessage();
    }

    protected function guessLineOfCode()
    {
        $pattern = sprintf('/%s/m', preg_quote($this->text, '/'));

        $rawContent = $this->markdown->getRawContent();
        $rawContent->replaceString("\r\n", "\n");
        $rawContent->replaceString("\r", "\n");
        $lines = $rawContent->split('/\n/');

        $lines->each(function (ElementLiteral $line, $index) use ($pattern) {
            if ($line->match($pattern)) {
                $this->markdownLineNo = ($index + 1);

                return false;
            }

            return true;
        });
    }

    protected function generateMessage()
    {
        $this->message = sprintf(
            '[%s] %s at line %d',
            $this->extension->getName(),
            rtrim($this->rawMessage, '.'),
            $this->markdownLineNo
        );
    }

}
