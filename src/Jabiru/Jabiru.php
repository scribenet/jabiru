<?php

namespace Scribe\Jabiru;

use Scribe\Jabiru\Component\Collection\Collection;
use Scribe\Jabiru\Component\Element\ElementLiteral;
use Scribe\Jabiru\Event\EmitterAwareInterface;
use Scribe\Jabiru\Extension\Core;
use Scribe\Jabiru\Extension\ExtensionInterface;
use Scribe\Jabiru\Renderer\RendererAwareInterface;
use Scribe\Jabiru\Renderer\HtmlRenderer;
use Scribe\Jabiru\Renderer\RendererInterface;

/**
 * Jabiru - The New Markdown Parser
 *
 * This is just the central point to manage `renderer` and `extensions`.
 *
 * The `Core` extensions are based on Markdown.pl
 * The `Gfm` extensions are based on Github Flavored Markdown
 */
class Jabiru
{

    const VERSION = '0.1.0-dev';

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var Collection|ExtensionInterface[]
     */
    private $extensions;

    /**
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer = null)
    {
        $this->extensions = new Collection();
        $this->renderer = $renderer;

        if (is_null($this->renderer)) {
            $this->setRenderer($this->getDefaultRenderer());
        }

        $this->addExtensions($this->getDefaultExtensions());
    }

    /**
     * @param string $text
     * @param array  $options
     *
     * @return string
     */
    public function render($text, array $options = array())
    {
        $text = new ElementLiteral($text);
        $markdown = new Markdown($this->renderer, $text, $options);

        $this->registerExtensions($markdown);

        $markdown->emit('initialize', array($text));
        $markdown->emit('block', array($text));
        $markdown->emit('finalize', array($text));

        return (string) $text;
    }

    /**
     * @param Scribe\Jabiru\Renderer\RendererInterface $renderer
     *
     * @return Jabiru
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * @return Scribe\Jabiru\Renderer\RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @param ExtensionInterface $extension
     *
     * @return Jabiru
     */
    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions->set($extension->getName(), $extension);

        return $this;
    }

    /**
     * @param ExtensionInterface[] $extensions
     *
     * @return Jabiru
     */
    public function addExtensions(array $extensions)
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }

        return $this;
    }

    /**
     * @param string|object $extension
     *
     * @return Jabiru
     */
    public function removeExtension($extension)
    {
        if ($extension instanceof ExtensionInterface) {
           $extension = $extension->getName();
        }

        $this->extensions->remove($extension);

        return $this;
    }

    /**
     * @param string|object $extension
     *
     * @return boolean
     */
    public function hasExtension($extension)
    {
        if ($extension instanceof ExtensionInterface) {
            $extension = $extension->getName();
        }

        return $this->extensions->exists($extension);
    }

    /**
     * @return RendererInterface
     */
    protected function getDefaultRenderer()
    {
        return new HtmlRenderer();
    }

    /**
     * @return ExtensionInterface[]
     */
    protected function getDefaultExtensions()
    {
        return array(
            new Core\WhitespaceExtension(),
            new Core\HeaderExtension(),
            new Core\ParagraphExtension(),
            new Core\HtmlBlockExtension(),
            new Core\LinkExtension(),
            new Core\HorizontalRuleExtension(),
            new Core\ListExtension(),
            new Core\CodeExtension(),
            new Core\BlockQuoteExtension(),
            new Core\ImageExtension(),
            new Core\InlineStyleExtension(),
            new Core\EscaperExtension()
        );
    }

    /**
     * @param Markdown $markdown
     */
    protected function registerExtensions(Markdown $markdown)
    {
        foreach ($this->extensions as $extension) {
            if ($extension instanceof RendererAwareInterface) {
                $extension->setRenderer($this->renderer);
            }

            if ($extension instanceof EmitterAwareInterface) {
                $extension->setEmitter($markdown);
            }

            $extension->register($markdown);
        }
    }

}