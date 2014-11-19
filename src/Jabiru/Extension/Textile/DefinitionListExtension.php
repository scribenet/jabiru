<?php

namespace Scribe\Jabiru\Extension\Textile;

use Scribe\Jabiru\Component\Element\Element;
use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Event\EmitterAwareInterface;
use Scribe\Jabiru\Event\EmitterAwareTrait;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Markdown;
use Scribe\Jabiru\Renderer\RendererAwareInterface;
use Scribe\Jabiru\Renderer\RendererAwareTrait;

/**
 * [Experimental] Textile Definition Lists
  */
class DefinitionListExtension implements ExtensionInterface, RendererAwareInterface, EmitterAwareInterface
{

    use RendererAwareTrait;
    use EmitterAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Markdown $markdown)
    {
        $markdown->on('block', array($this, 'processDefinitionList'), 30);
        $markdown->on('block', array($this, 'processWikiDefinitionList'), 30);
    }

    /**
     * @param ElementLiteral $text
     */
    public function processDefinitionList(ElementLiteral $text)
    {
        $text->replace(
            '{
                (                    #1 whole match
                    - [ \t]*         #  dL starts with a dash
                    (.+?) [ \t]* :=  #1 DT [dt and dd are splitted by :=]
                    (                #2 DD
                        \n? .+? =:   #  Multiline contents will ends with =:
                        |
                        [^\n]+?      #  A line
                    )
                    \n
                ){1,}
                \n+
            }smx',
            function (ElementLiteral $w) {
                $this->processListItems($w);
                $dl = (new Element('dl'))->setInner($w->trim()->wrap("\n", "\n"));

                return $dl->render();
            }
        );
    }

    /**
     * @param ElementLiteral $text
     */
    public function processListItems(ElementLiteral $text)
    {
        /** @noinspection PhpUnusedParameterInspection */
        $text->replace(
            '{
                (                        #1 whole match
                    - [ \t]*             #  dL starts with a dash
                    ([^\n]+) [ \t]* :=   #2 DT [dt and dd are splitted by :=]
                    (                    #3
                        \n (.+) (=:)     #4 DD>P
                        |
                        [ \t]* ([^\n]+)  #6 DD
                    )
                )
                \n
            }smx',
            function (ElementLiteral $w, ElementLiteral $item, ElementLiteral $definition, ElementLiteral $c, ElementLiteral $dd1, ElementLiteral $multiLine, ElementLiteral $dd2 = null) {
                $dt = (new Element('dt'))->setInner($definition->trim());
                $dd = (new Element('dd'));

                if (!$multiLine->isEmpty()) {
                    $dd1->trim();
                    $this->getEmitter()->emit('outdent', [$dd1]);
                    $dd->setInner($this->getRenderer()->renderParagraph($dd1));
                } else {
                    $dd->setInner($dd2->trim());
                }

                return $dt->render() . "\n" . $dd->render() . "\n";
            }
        );
    }

    /**
     * @param ElementLiteral $text
     */
    public function processWikiDefinitionList(ElementLiteral $text)
    {
        $text->replace(
            '{
                (^
                    ;[ \t]*.+\n
                    (:[ \t]*.+\n){1,}
                ){1,}
                \n+
            }mx',
            function (ElementLiteral $w) {
                $w->replace('/^;[ \t]*(.+)\n((:[ \t]*.+\n){1,})/m', function (ElementLiteral $w, ElementLiteral $item, ElementLiteral $content) {
                    $dt = (new Element('dt'))->setInner($item);
                    $lines = $content->trim()->ltrim(':')->split('/\n?^:[ \t]*/m', PREG_SPLIT_NO_EMPTY);

                    if (count($lines) > 1) {
                        $dd = (new Element('dd'))->setInner(
                            (new Element('p'))->setInner(
                                trim($lines->join($this->getRenderer()->renderLineBreak() . "\n"))
                            )
                        );
                    } else {
                        $dd = (new Element('dd'))->setInner($content->trim());
                    }

                    return $dt->render() . "\n" . $dd->render() . "\n";
                });

                $tag = (new Element('dl'))->setInner("\n" . $w->trim() . "\n");

                return $tag->render();
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'definitionList';
    }

}