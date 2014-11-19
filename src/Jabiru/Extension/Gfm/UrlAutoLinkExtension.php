<?php

namespace Scribe\Jabiru\Extension\Gfm;

use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Markdown;

/**
 * Turn standard URL into markdown URL (http://example.com -> <http://example.com>)
 */
class UrlAutoLinkExtension implements ExtensionInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Markdown $markdown)
    {
        $markdown->on('inline', array($this, 'processStandardUrl'), 35);
    }

    /**
     * Turn standard URL into markdown URL
     *
     * @param ElementLiteral $text
     */
    public function processStandardUrl(ElementLiteral $text)
    {
        $hashes = array();

        // escape <code>
        $text->replace('{<code>.*?</code>}m', function (ElementLiteral $w) use (&$hashes) {
            $md5 = md5($w);
            $hashes[$md5] = $w;

            return "{gfm-extraction-$md5}";
        });

        $text->replace('{(?<!]\(|"|<|\[)((?:https?|ftp)://[^\'">\s]+)(?!>|\"|\])}', '<\1>');

        /** @noinspection PhpUnusedParameterInspection */
        $text->replace('/\{gfm-extraction-([0-9a-f]{32})\}/m', function (ElementLiteral $w, ElementLiteral $md5) use (&$hashes) {
            return $hashes[(string)$md5];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'urlAutoLink';
    }

}