<?php

namespace Scribe\Jabiru\Event;

/**
 * Implementation of EmitterAwareInterface
 */
trait EmitterAwareTrait
{

    /**
     * @var EmitterInterface
     */
    private $emitter;

    /**
     * @param EmitterInterface $emitter
     */
    public function setEmitter(EmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * @return EmitterInterface
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

}