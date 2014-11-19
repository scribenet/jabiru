<?php

namespace Scribe\Jabiru\Extension\Gfm;

use Scribe\Jabiru\Component\Element\Element;
use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Extension\Core\ListExtension;

/**
 * Further, lists can be turned into Task Lists by prefacing list items with [ ] or [x] (incomplete or complete, respectively).
 *
 * This extension replaces Core\ListExtension
 */
class TaskListExtension extends ListExtension
{

    /**
     * {@inheritdoc}
     */
    public function processListItems(ElementLiteral $list, array $options = array(), $level = 0)
    {
        $list->replace('/\n{2,}\z/', "\n");

        /** @noinspection PhpUnusedParameterInspection */
        $list->replace('{
            (\n)?                                 # leading line = $1
            (^[ \t]*)                             # leading whitespace = $2
            (' . $this->getPattern() . ') [ \t]+  # list marker = $3
            (([ ]*(\[([ ]|x)\]) [ \t]+)?(?s:.+?)  # list item text = $4, checkbox = $5, checked = %6
            (\n{1,2}))
            (?= \n* (\z | \2 (' . $this->getPattern() . ') [ \t]+))
        }mx', function (ElementLiteral $w, ElementLiteral $leadingLine, ElementLiteral $ls, ElementLiteral $m, ElementLiteral $item, ElementLiteral $checkbox, ElementLiteral $check) use ($options, $level) {
            if (!$checkbox->isEmpty()) {
                $item->replace('/^\[( |x)\]/', function (ElementLiteral $w, ElementLiteral $check) {
                    $attr = array('type' => 'checkbox');

                    if ($check == 'x') {
                        $attr['checked'] = 'checked';
                    }

                    return $this->getRenderer()->renderTag('input', new ElementLiteral(), Element::TYPE_INLINE, array('attr' => $attr));
                });
            }

            if ((string)$leadingLine || $item->match('/\n{2,}/')) {
                $this->getEmitter()->emit('outdent', array($item));
                $this->getEmitter()->emit('block', array($item));
            } else {
                $this->getEmitter()->emit('outdent', array($item));
                $this->processList($item, $options, ++$level);
                $item->rtrim();
                $this->getEmitter()->emit('inline', array($item));
            }

            return $this->getRenderer()->renderListItem($item) . "\n";
        });
    }

}