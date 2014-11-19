<?php

namespace Scribe\Jabiru\Extension\Gfm;

use Scribe\Jabiru\Component\Collection\Collection;
use Scribe\Jabiru\Component\Element\Element;
use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Exception\SyntaxError;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Markdown;
use Scribe\Jabiru\Renderer\HtmlRenderer;
use Scribe\Jabiru\Renderer\RendererAwareInterface;
use Scribe\Jabiru\Renderer\RendererAwareTrait;

/**
 * Gfm tables
 */
class TableExtension implements ExtensionInterface, RendererAwareInterface
{

    use RendererAwareTrait;

    /**
     * @var Markdown
     */
    private $markdown;

    /**
     * @var string
     */
    private $hash;

    /**
     * {@inheritdoc}
     */
    public function register(Markdown $markdown)
    {
        $this->markdown = $markdown;
        $this->hash = '{gfm:table:escape(' . md5('|') . ')}';

        if ($this->getRenderer() instanceof HtmlRenderer) {
            // Output format depends on HTML for now
            $markdown->on('block', array($this, 'processTable'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'table'; // Not gfmTable
    }

    /**
     * Gfm tables
     *
     * @param Text  $text
     * @param array $options
     */
    public function processTable(ElementLiteral $text, array $options = array())
    {
        $lessThanTab = $options['tabWidth'] - 1;

        $text->replace('/
            (?:\n\n|\A)
            (?:[ ]{0,' . $lessThanTab . '}      #  table header
                (?:\|?)                         #  optional outer pipe
                ([^\n]*?\|[^\n]*?)              #1 table header
                (?:\|?)                         #  optional outer pipe
            )\n
            (?:[ ]{0,' . $lessThanTab . '}      #  second line
                (?:\|?)                         #  optional outer pipe
                ([-:\| ]+?\|[-:\| ]+?)          #2 dashes and pipes
                (?:\|?)                         #  optional outer pipe
            )\n
            (.*?)\n{2,}                         #3 table body
        /smx', function (ElementLiteral $w, ElementLiteral $header, ElementLiteral $rule, ElementLiteral $body) use ($options) {
            // Escape pipe to hash, so you can include pipe in cells by escaping it like this: `\\|`
            $this->escapePipes($header);
            $this->escapePipes($rule);
            $this->escapePipes($body);

            try {
                $baseTags    = $this->createBaseTags($rule->split('/\|/'));
                $headerCells = $this->parseHeader($header, $baseTags);
                $bodyRows    = $this->parseBody($body, $baseTags);
            } catch (SyntaxError $e) {
                if ($options['strict']) {
                    throw $e;
                }

                return $w;
            }

            $html = $this->createView($headerCells, $bodyRows);
            $this->unescapePipes($html);

            return "\n\n" . $html . "\n\n";
        });
    }

    /**
     * @param Collection $headerCells
     * @param Collection $bodyRows
     *
     * @return Text
     */
    protected function createView(Collection $headerCells, Collection $bodyRows)
    {
        $tHeadRow = new Element('tr');
        $tHeadRow->setInner("\n" . $headerCells->join("\n") . "\n");

        $tHead = new Element('thead');
        $tHead->setInner("\n" . $tHeadRow . "\n");

        $tBody = new Element('tbody');

        $bodyRows->apply(function (Collection $row) use (&$options) {
            $tr = new Element('tr');
            $tr->setInner("\n" . $row->join("\n") . "\n");

            return $tr;
        });

        $tBody->setInner("\n" .$bodyRows->join("\n") . "\n");

        $table = new Element('table');
        $table->setInner("\n" . $tHead . "\n" . $tBody . "\n");

        return new ElementLiteral($table->render());
    }

    /**
     * @param Collection $rules
     *
     * @return Collection|Scribe\Jabiru\Common\Tag[]
     */
    protected function createBaseTags(Collection $rules)
    {
        /* @var Collection|Tag[] $baseTags */
        $baseTags = new Collection();

        $rules->each(function (ElementLiteral $cell) use (&$baseTags) {
            $cell->trim();
            $tag = new Element('td');

            if ($cell->match('/^-.*:$/')) {
                $tag->setAttribute('align', 'right');
            } elseif ($cell->match('/^:.*:$/')) {
                $tag->setAttribute('align', 'center');
            }

            $baseTags->add($tag);
        });

        return $baseTags;
    }

    /**
     * @param Text       $header
     * @param Collection $baseTags
     *
     * @throws Scribe\Jabiru\Exception\SyntaxError
     *
     * @return Collection
     */
    protected function parseHeader(ElementLiteral $header, Collection $baseTags)
    {
        $cells = new Collection();

        try {
            $header->split('/\|/')->each(function (ElementLiteral $cell, $index) use ($baseTags, &$cells) {
                /* @var Tag $tag */
                $tag = clone $baseTags->get($index);
                $tag->setName('th');
                $this->markdown->emit('inline', array($cell));
                $tag->setInner($cell->trim());

                $cells->add($tag);
            });
        } catch (\OutOfBoundsException $e) {
            throw new SyntaxError(
                'Too much cells on table header.',
                $this, $header, $this->markdown, $e
            );
        }

        if ($baseTags->count() != $cells->count()) {
            throw new SyntaxError(
                'Unexpected number of table cells in header.',
                $this, $header, $this->markdown
            );
        }

        return $cells;
    }

    /**
     * @param Text       $body
     * @param Collection $baseTags
     *
     * @return Collection
     */
    protected function parseBody(ElementLiteral $body, Collection $baseTags)
    {
        $rows = new Collection();

        $body->split('/\n/')->each(function (ElementLiteral $row, $index) use ($baseTags, &$rows) {
            $row->trim()->trim('|');

            $cells = new Collection();

            try {
                $row->split('/\|/')->each(function (ElementLiteral $cell, $index) use (&$baseTags, &$cells) {
                    /* @var Tag $tag */
                    $tag = clone $baseTags->get($index);
                    $this->markdown->emit('inline', array($cell));
                    $tag->setInner($cell->trim());

                    $cells->add($tag);
                });
            } catch (\OutOfBoundsException $e) {
                throw new SyntaxError(
                    sprintf('Too much cells on table body (row #%d).', $index),
                    $this, $row, $this->markdown, $e
                );
            }

            if ($baseTags->count() != $cells->count()) {
                throw new SyntaxError(
                    'Unexpected number of table cells in body.',
                    $this, $row, $this->markdown
                );
            }

            $rows->add($cells);
        });

        return $rows;
    }

    /**
     * @param ElementLiteral $text
     */
    protected function escapePipes(ElementLiteral $text)
    {
        $text->replaceString('\\|', $this->hash);
    }

    /**
     * @param ElementLiteral $text
     */
    protected function unescapePipes(ElementLiteral $text)
    {
        $text->replaceString($this->hash, '|');
    }

}