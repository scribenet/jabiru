<?php

namespace Scribe\Jabiru\Renderer;

/**
 * Implementation of RendererAwareInterface
 */
trait RendererAwareTrait
{

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @return RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

}