<?php

namespace Scribe\Jabiru\Extension\Html;

use Scribe\Jabiru\Component\Element\Element;
use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Markdown;

/**
 * [Experimental] Emmet-style HTML attributes
  */
class AttributesExtension implements ExtensionInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Markdown $markdown)
    {
        $markdown->on('tag', array($this, 'processBlockTags'));
    }

    /**
     * Parse emmet style attributes
     *
     * @param Tag $tag
     */
    public function processBlockTags(Element $tag)
    {
        if ($tag->isInline()) {
            return;
        }

        $text = null;
        $tag->getInner()->replace('/(^{([^:\(\)]+)}[ \t]*\n?|(?:[ \t]*|\n?){([^:\(\)]+)}\n*$)/', function (ElementLiteral $w) use (&$text) {
            $text = $w->trim()->trim('{}');

            return '';
        });

        if ($text) {
            $tag->setAttributes($this->parseAttributes($text));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'htmlAttributes';
    }

    /**
     * @param ElementLiteral $text
     *
     * @return array
     */
    protected function parseAttributes(ElementLiteral $text)
    {
        $patterns = [
            'id'    => '/^#([a-zA-Z0-9_-]+)/',
            'class' => '/^\.([a-zA-Z0-9_-]+)/',
            'attr'  => '/^\[([^\]]+)\]/',
            'ident' => '/^(.)/'
        ];

        $tokens = [
            'id' => [], 'class' => [], 'attr' => [], 'ident' => []
        ];

        while (!$text->isEmpty()) {
            foreach ($patterns as $name => $pattern) {
                if ($text->match($pattern, $matches)) {
                    $tokens[$name][] = $matches[1];
                    $text->setString(
                        substr($text->getString(), strlen($matches[0]))
                    );

                    break;
                }
            }
        }

        $attributes = array();

        if (count($tokens['id'])) {
            $attributes['id'] = array_pop($tokens['id']);
        }

        if (count($tokens['class'])) {
            $attributes['class'] = implode(' ', $tokens['class']);
        }

        if (count($tokens['attr'])) {
            foreach ($tokens['attr'] as $raw) {
                $items = explode(' ', $raw);
                foreach ($items as $item) {
                    if (strpos($item, '=') !== false) {
                        list ($key, $value) = explode('=', $item);
                        $attributes[$key] = trim($value, '"');
                    } else {
                        $attributes[$item] = $item;
                    }
                }
            }
        }

        return $attributes;
    }

}