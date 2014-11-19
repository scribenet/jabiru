<?php

namespace Scribe\Jabiru\Renderer;

/**
 * RendererAwareInterface should be implemented by extension classes that depends on RendererInterface
 */
interface RendererAwareInterface
{

    /**
     * @api
     *
     * @return RendererInterface
     */
    public function getRenderer();

    /**
     * @api
     *
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer);

}